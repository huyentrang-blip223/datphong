<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CashPaymentService
{
    public function select(Model $subject, User $guest): Model
    {
        return DB::transaction(function () use ($subject, $guest) {
            $locked = $subject->newQuery()->whereKey($subject->id)->lockForUpdate()->firstOrFail();
            abort_unless($guest->role === 'guest' && $this->customerId($locked) === (int) $guest->id, 403);
            $this->assertOpen($locked);
            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Giao dịch đã được thanh toán.');
            }
            $existing = $locked->payments()->where('gateway', 'cash')->where('status', 'initiated')->first();
            if ($existing) {
                return $existing;
            }
            $payment = $locked->payments()->create([
                'gateway' => 'cash', 'txn_ref' => 'CASH-'.strtoupper(Str::random(20)),
                'amount' => $locked->total_amount, 'status' => 'initiated', 'paid_at' => null,
            ]);
            if ($locked instanceof Booking) {
                app(BookingLifecycleService::class)->confirmLocked($locked, $guest, 'cash_payment_selected');
            } elseif ($locked->status === 'pending') {
                $locked->update(['status' => 'confirmed']);
            }
            app(AuditLogService::class)->record($guest, 'cash_payment_selected', $payment->getTable(), $payment->id);
            app(NotificationService::class)->send($guest, 'cash_payment_selected', 'Đã chọn thanh toán tiền mặt', 'Vui lòng thanh toán trực tiếp cho đơn vị cung cấp. Chưa ghi nhận đã thu tiền.');

            return $payment;
        }, 3);
    }

    public function collect(Model $subject, User $actor): Model
    {
        return DB::transaction(function () use ($subject, $actor) {
            $locked = $subject->newQuery()->whereKey($subject->id)->lockForUpdate()->firstOrFail();
            $this->authorizeOperator($locked, $actor);
            // Cash for delivered orders can be collected at the moment of handover.
            $this->assertOpen($locked, true);
            $payment = $locked->payments()->where('gateway', 'cash')->where('status', 'initiated')->lockForUpdate()->first();
            if (! $payment || $locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Không có khoản tiền mặt đang chờ thu hoặc đã ghi nhận thanh toán.');
            }
            if ((string) $payment->amount !== (string) $locked->total_amount) {
                throw new InvalidArgumentException('Số tiền không khớp, cần kiểm tra trước khi xác nhận.');
            }
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            if ($locked instanceof Booking) {
                app(LoyaltyService::class)->award($this->customerId($locked), (int) floor((float) $locked->total_amount / 10000), 'cash_payment_booking', $locked->id);
            } elseif ($locked instanceof ProductOrder && $locked->status === 'delivered') {
                app(LoyaltyService::class)->award($this->customerId($locked), (int) floor((float) $locked->total_amount / 10000), 'product_order_delivered', null, $locked->id);
            }
            app(AuditLogService::class)->record($actor, 'cash_payment_paid', $payment->getTable(), $payment->id, ['status' => 'initiated'], ['status' => 'paid', 'amount' => $payment->amount]);
            app(NotificationService::class)->send($this->customerId($locked), 'payment_paid', 'Đã nhận tiền mặt', 'Đơn vị cung cấp đã xác nhận nhận đủ tiền thanh toán.');

            return $payment;
        }, 3);
    }

    public function refund(Model $subject, User $actor): Model
    {
        return DB::transaction(function () use ($subject, $actor) {
            $locked = $subject->newQuery()->whereKey($subject->id)->lockForUpdate()->firstOrFail();
            $this->authorizeOperator($locked, $actor);
            if (! in_array($locked->status, ['cancelled', 'expired', 'refunded'], true)) {
                throw new InvalidArgumentException('Chỉ xác nhận trả tiền mặt sau khi hủy.');
            }
            $payment = $locked->payments()->where('gateway', 'cash')->where('status', 'paid')->lockForUpdate()->first();
            if (! $payment) {
                $refunded = $locked->payments()->where('gateway', 'cash')->where('status', 'refunded')->first();
                if ($refunded) {
                    return $refunded;
                }
                throw new InvalidArgumentException('Không có khoản tiền mặt đã thu cần hoàn.');
            }
            $payment->update(['status' => 'refunded']);
            if ($locked instanceof Booking) {
                app(LoyaltyService::class)->reverse($this->customerId($locked), $locked->id);
                BookingStatusLog::create(['booking_id' => $locked->id, 'from_status' => $locked->status,
                    'to_status' => 'refunded', 'actor_id' => $actor->id, 'reason' => 'cash_payment_refunded']);
                $locked->update(['status' => 'refunded']);
            }
            app(AuditLogService::class)->record($actor, 'cash_payment_refunded', $payment->getTable(), $payment->id, ['status' => 'paid'], ['status' => 'refunded']);
            app(NotificationService::class)->send($this->customerId($locked), 'payment_refunded', 'Đã hoàn tiền mặt', 'Đơn vị cung cấp đã xác nhận trả lại tiền mặt.');

            return $payment;
        }, 3);
    }

    private function customerId(Model $subject): int
    {
        return (int) ($subject instanceof ProductOrder ? $subject->buyer_id : $subject->guest_id);
    }

    private function authorizeOperator(Model $subject, User $actor): void
    {
        if ($subject instanceof ProductOrder) {
            $owns = $actor->role === 'seller' && $subject->items()->exists()
                && ! $subject->items()->whereHas('product', fn ($q) => $q->where('seller_id', '<>', $actor->id))->exists();
        } else {
            $ownerId = $subject instanceof Booking ? $subject->room->homestay->owner_id : $subject->experience->homestay->owner_id;
            $owns = $actor->role === 'host' && (int) $ownerId === (int) $actor->id;
        }
        abort_unless($owns, 403);
    }

    private function assertOpen(Model $subject, bool $collecting = false): void
    {
        if ($subject instanceof Booking) {
            app(BookingLifecycleService::class)->assertPayable($subject);
        } elseif ($subject instanceof ExperienceBooking) {
            $experience = Experience::whereKey($subject->experience_id)->lockForUpdate()->firstOrFail();
            if (! in_array($subject->status, ['pending', 'confirmed'], true) || $experience->status === 'cancelled'
                || (! $collecting && $experience->start_at->lte(now()))) {
                throw new InvalidArgumentException('Đặt chỗ không còn cho phép thanh toán.');
            }
        } elseif (! in_array($subject->status, $collecting ? ['confirmed', 'packing', 'delivered'] : ['pending', 'confirmed'], true)) {
            throw new InvalidArgumentException('Đơn không còn cho phép thanh toán.');
        }
    }
}
