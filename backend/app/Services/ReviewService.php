<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use InvalidArgumentException;

class ReviewService
{
    public function guestToHost(Booking $booking, User $guest, int $rating, ?string $content): Review
    {
        $booking->load('room.homestay');

        if ((int) $booking->guest_id !== (int) $guest->id || $guest->role !== 'guest') {
            throw new InvalidArgumentException('Không có quyền đánh giá booking này.');
        }
        if ($booking->status !== 'completed') {
            throw new InvalidArgumentException('Chỉ booking completed được đánh giá.');
        }

        return Review::create([
            'booking_id' => $booking->id,
            'from_user_id' => $guest->id,
            'to_user_id' => $booking->room->homestay->owner_id,
            'direction' => 'guest_to_host',
            'rating' => $rating,
            'content' => $content,
            'visible' => true,
        ]);
    }
}
