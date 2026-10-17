<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewService
{
    public function __construct(
        private NotificationService $notificationService,
        private AuditLogService $auditLogService
    ) {
    }

    public function guestToHost(Booking $booking, User $guest, int $rating, ?string $content): Review
    {
        return DB::transaction(function () use ($booking, $guest, $rating, $content) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $booking->load('room.homestay');

            if ((int) $booking->guest_id !== (int) $guest->id || $guest->role !== 'guest') {
                throw new InvalidArgumentException('Không có quyền đánh giá booking này.');
            }
            if ($booking->status !== 'completed') {
                throw new InvalidArgumentException('Chỉ booking completed được đánh giá.');
            }
            if ($rating < 1 || $rating > 5 || $booking->reviews()->where('direction', 'guest_to_host')->exists()) {
                throw new InvalidArgumentException('Điểm không hợp lệ hoặc booking đã được đánh giá.');
            }

            $review = Review::create([
                'booking_id' => $booking->id,
                'from_user_id' => $guest->id,
                'to_user_id' => $booking->room->homestay->owner_id,
                'direction' => 'guest_to_host',
                'rating' => $rating,
                'content' => $content,
                'visible' => true,
            ]);

            $homestay = \App\Models\Homestay::whereKey($booking->room->homestay_id)->lockForUpdate()->firstOrFail();
            $homestay->update(['avg_rating' => Review::where('direction', 'guest_to_host')->where('visible', true)
                ->whereHas('booking.room', fn ($q) => $q->where('homestay_id', $homestay->id))->avg('rating') ?? 0]);

            $this->notificationService->send((int) $booking->room->homestay->owner_id, 'review_created', 'Có đánh giá mới', 'Booking '.$booking->code.' đã có đánh giá '.$rating.' sao.', 'review', $review->id);
            $this->auditLogService->record($guest, 'review_created', 'reviews', $review->id, null, [
                'booking_id' => $booking->id,
                'rating' => $rating,
            ]);

            return $review;
        }, 3);
    }
}
