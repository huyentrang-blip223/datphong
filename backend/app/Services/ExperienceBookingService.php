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

            if ($locked->status !== 'open' || $remaining < $peopleCount) {
                throw new AvailabilityConflictException('Trải nghiệm không còn đủ chỗ.');
            }

            $booking = ExperienceBooking::create([
                'experience_id' => $locked->id,
                'guest_id' => $guest->id,
                'people_count' => $peopleCount,
                'total_amount' => (float) $locked->price * $peopleCount,
                'status' => 'confirmed',
            ]);

            $locked->booked_count += $peopleCount;
            if ($locked->booked_count >= $locked->capacity) {
                $locked->status = 'full';
            }
            $locked->save();

            return $booking;
        });
    }
}
