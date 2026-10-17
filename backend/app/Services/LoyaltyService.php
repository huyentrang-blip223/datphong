<?php
namespace App\Services;

use App\Models\LoyaltyTransaction;
use App\Models\User;

class LoyaltyService
{
    public function award(User|int $user, int $points, string $reason, ?int $bookingId = null, ?int $orderId = null): ?LoyaltyTransaction
    {
        if ($points <= 0) {
            return null;
        }

        $userId = $user instanceof User ? $user->id : $user;
        $current = (int) LoyaltyTransaction::where('user_id', $userId)->latest('id')->value('balance_after');

        return LoyaltyTransaction::create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'order_id' => $orderId,
            'points_delta' => $points,
            'balance_after' => $current + $points,
            'reason' => $reason,
        ]);
    }
}
