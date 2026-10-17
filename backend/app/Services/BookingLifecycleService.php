<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\RoomAvailability;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingLifecycleService
{
    public function expireDue(?int $roomId = null): int
    {
        $count = 0;
        Booking::whereIn('status', ['holding', 'pending_payment'])
            ->where('hold_expires_at', '<=', now())
            ->when($roomId, fn ($q) => $q->where('room_id', $roomId))
            ->select('id')->chunkById(100, function ($bookings) use (&$count) {
                foreach ($bookings as $booking) {
                    $count += DB::transaction(function () use ($booking) {
                        $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
                        if (! in_array($locked->status, ['holding', 'pending_payment'], true)
                            || ! $locked->hold_expires_at || $locked->hold_expires_at->isFuture()) {
                            return 0;
                        }
                        $this->release($locked, 'units_held');
                        $this->change($locked, 'expired', null, 'hold_expired');

                        return 1;
                    }, 3);
                }
            });

        return $count;
    }

    // Caller must hold the booking lock. All paths lock booking before inventory/payment.
    public function assertPayable(Booking $booking): void
    {
        if (! in_array($booking->status, ['holding', 'pending_payment', 'confirmed'], true)
            || ($booking->hold_expires_at && $booking->hold_expires_at->lte(now()))) {
            throw new InvalidArgumentException('Booking đã hết hạn hoặc không còn cho phép thanh toán.');
        }
    }

    public function confirmLocked(Booking $booking, User $actor): void
    {
        $this->assertPayable($booking);
        if (in_array($booking->status, ['holding', 'pending_payment'], true)) {
            foreach ($this->inventory($booking) as $row) {
                if ($row->units_held < 1) {
                    throw new InvalidArgumentException('Không còn tồn giữ chỗ hợp lệ.');
                }
                $row->update(['units_held' => $row->units_held - 1, 'units_sold' => $row->units_sold + 1]);
            }
            $this->change($booking, 'confirmed', $actor, 'payment_confirmed');
        }
    }

    public function cancel(Booking $booking, User $actor): Booking
    {
        abort_unless($actor->role === 'guest' && (int) $booking->guest_id === (int) $actor->id, 403);

        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['cancelled', 'refunded', 'expired'], true)) {
                return $locked;
            }
            if (! in_array($locked->status, ['holding', 'pending_payment', 'confirmed'], true)) {
                throw new InvalidArgumentException('Trạng thái booking không cho phép hủy.');
            }
            $paid = $locked->payments()->where('status', 'paid')->exists();
            if ($locked->checkin_date->lte(now()) || ($paid && now()->gt($locked->checkin_date->copy()->subHours(24)))) {
                throw new InvalidArgumentException('Chỉ hủy trước ngày nhận phòng; booking đã trả tiền phải hủy trước ít nhất 24 giờ.');
            }
            $this->release($locked, $locked->status === 'confirmed' ? 'units_sold' : 'units_held');
            $locked->cancel_reason = 'guest_cancelled';
            $this->change($locked, 'cancelled', $actor, 'guest_cancelled');
            app(NotificationService::class)->send((int) $locked->room->homestay->owner_id, 'booking_cancelled', 'Khách đã hủy booking', $locked->code, 'booking', $locked->id);

            return $locked;
        }, 3);
    }

    public function complete(Booking $booking, User $actor): Booking
    {
        abort_unless($actor->role === 'host' && (int) $booking->room->homestay->owner_id === (int) $actor->id, 403);

        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'confirmed' || $locked->checkout_date->isFuture()
                || ! $locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Chỉ hoàn tất booking đã thanh toán, confirmed và đã đến ngày trả phòng.');
            }
            $this->change($locked, 'completed', $actor, 'host_completed_stay');

            return $locked;
        }, 3);
    }

    public function refund(Booking $booking, User $actor): Booking
    {
        abort_unless($actor->role === 'admin' || ($actor->role === 'guest' && (int) $booking->guest_id === (int) $actor->id), 403);

        $payment = $booking->payments()->where('status', 'paid')->first();
        if ($payment?->gateway === 'vnpay') {
            // Persist request tracking outside the final DB transaction: an HTTP
            // timeout must not roll back evidence of a possibly accepted refund.
            app(VnPayGatewayService::class)->refund($payment, $actor);
        }

        return DB::transaction(function () use ($booking, $actor) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'refunded') {
                return $locked;
            }
            $payment = $locked->payments()->where('status', 'paid')->lockForUpdate()->first();
            if (! in_array($locked->status, ['cancelled', 'expired'], true) || ! $payment) {
                throw new InvalidArgumentException('Chỉ hoàn tiền booking đã thanh toán và đã hủy theo chính sách.');
            }
            // mock_test remains a ledger simulation; VNPay requires verified sandbox success.
            $payment->update(['status' => 'refunded']);
            app(LoyaltyService::class)->reverse((int) $locked->guest_id, $locked->id);
            $this->change($locked, 'refunded', $actor, $payment->gateway === 'vnpay' ? 'vnpay_sandbox_refund_full' : 'sandbox_refund_full');

            return $locked;
        }, 3);
    }

    private function inventory(Booking $booking)
    {
        return RoomAvailability::where('room_id', $booking->room_id)
            ->where('stay_date', '>=', $booking->checkin_date->toDateString())
            ->where('stay_date', '<', $booking->checkout_date->toDateString())
            ->orderBy('stay_date')->lockForUpdate()->get();
    }

    private function release(Booking $booking, string $counter): void
    {
        foreach ($this->inventory($booking) as $row) {
            if ($row->{$counter} < 1) {
                throw new InvalidArgumentException('Tồn phòng không khớp booking; cần đối soát trước khi tiếp tục.');
            }
            $row->decrement($counter);
        }
    }

    private function change(Booking $booking, string $status, ?User $actor, string $reason): void
    {
        $from = $booking->status;
        $booking->status = $status;
        $booking->hold_expires_at = null;
        $booking->save();
        BookingStatusLog::create(['booking_id' => $booking->id, 'from_status' => $from, 'to_status' => $status, 'actor_id' => $actor?->id, 'reason' => $reason]);
        app(AuditLogService::class)->record($actor, $reason, 'bookings', $booking->id, ['status' => $from], ['status' => $status]);
        app(NotificationService::class)->send((int) $booking->guest_id, 'booking_'.$status, 'Cập nhật booking '.$booking->code, 'Trạng thái: '.$status, 'booking', $booking->id);
    }
}
