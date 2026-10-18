<?php

namespace App\Services;

use App\Exceptions\AvailabilityConflictException;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExperienceBookingService
{
    public function createBooking(User $guest, Experience $experience, int $peopleCount): ExperienceBooking
    {
        if ($guest->role !== 'guest') {
            throw new InvalidArgumentException('Chỉ tài khoản guest được đặt trải nghiệm.');
        }

        if ($peopleCount < 1 || $peopleCount > 20) {
            throw new InvalidArgumentException('Số người tham gia không hợp lệ.');
        }

        return DB::transaction(function () use ($guest, $experience, $peopleCount) {
            $locked = Experience::whereKey($experience->id)->lockForUpdate()->firstOrFail();
            $remaining = (int) $locked->capacity - (int) $locked->booked_count;

            if ($locked->status !== 'open' || $locked->start_at->lte(now()) || $remaining < $peopleCount) {
                throw new AvailabilityConflictException('Trải nghiệm không còn đủ chỗ.');
            }

            $booking = ExperienceBooking::create([
                'experience_id' => $locked->id,
                'guest_id' => $guest->id,
                'people_count' => $peopleCount,
                'total_amount' => (float) $locked->price * $peopleCount,
                'status' => 'pending',
            ]);

            $locked->booked_count += $peopleCount;
            if ($locked->booked_count >= $locked->capacity) {
                $locked->status = 'full';
            }
            $locked->save();

            return $booking;
        });
    }

    public function cancel(ExperienceBooking $booking, User $actor): ExperienceBooking
    {
        abort_unless($actor->role === 'guest' && (int) $booking->guest_id === (int) $actor->id, 403);

        return DB::transaction(function () use ($booking, $actor) {
            $locked = ExperienceBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'cancelled') {
                return $locked;
            }
            $experience = Experience::whereKey($locked->experience_id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, ['pending', 'confirmed'], true) || $experience->start_at->lte(now())) {
                throw new InvalidArgumentException('Chỉ hủy trải nghiệm trước giờ bắt đầu.');
            }
            $paid = $locked->payments()->where('status', 'paid')->lockForUpdate()->get();
            if ($paid->isNotEmpty() && now()->gt($experience->start_at->copy()->subHours(24))) {
                throw new InvalidArgumentException('Trải nghiệm đã trả tiền phải hủy trước ít nhất 24 giờ.');
            }
            $experience->booked_count -= $locked->people_count;
            if ($experience->status === 'full') {
                $experience->status = 'open';
            }
            $experience->save();
            $locked->update(['status' => 'cancelled']);
            app(AuditLogService::class)->record($actor, 'experience_cancelled', 'experience_bookings', $locked->id);
            app(NotificationService::class)->send($actor, 'experience_cancelled', 'Đã hủy trải nghiệm', 'Chỗ đã được trả lại. Nếu đã thanh toán, vui lòng liên hệ chủ homestay để nhận lại tiền.', 'experience_booking', $locked->id);

            return $locked;
        }, 3);
    }

    public function complete(ExperienceBooking $booking, User $host): ExperienceBooking
    {
        abort_unless($host->role === 'host' && (int) $booking->experience->homestay->owner_id === (int) $host->id, 403);

        return DB::transaction(function () use ($booking, $host) {
            $locked = ExperienceBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $experience = Experience::whereKey($locked->experience_id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'confirmed' || ! $locked->payments()->where('status', 'paid')->exists()
                || $experience->start_at->copy()->addMinutes($experience->duration_minutes)->isFuture()) {
                throw new InvalidArgumentException('Chỉ hoàn tất đặt chỗ đã trả tiền sau khi trải nghiệm kết thúc.');
            }
            $locked->update(['status' => 'completed']);
            app(AuditLogService::class)->record($host, 'experience_booking_completed', 'experience_bookings', $locked->id);
            app(NotificationService::class)->send((int) $locked->guest_id, 'experience_completed', 'Trải nghiệm đã hoàn tất', $experience->title, 'experience_booking', $locked->id);

            return $locked;
        }, 3);
    }
}
