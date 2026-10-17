<?php

namespace App\Services;

use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function award(User|int $user, int $points, string $reason, ?int $bookingId = null, ?int $orderId = null): ?LoyaltyTransaction
    {
        if ($points <= 0) {
            return null;
        }

        $userId = $user instanceof User ? $user->id : $user;

        return DB::transaction(function () use ($userId, $points, $reason, $bookingId, $orderId) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            if (($bookingId || $orderId) && LoyaltyTransaction::where('user_id', $userId)
                ->when($bookingId, fn ($q) => $q->where('booking_id', $bookingId))
                ->when($orderId, fn ($q) => $q->where('order_id', $orderId))
                ->where('points_delta', '>', 0)->exists()) {
                return null;
            }
            $current = (int) LoyaltyTransaction::where('user_id', $userId)->sum('points_delta');

            return LoyaltyTransaction::create([
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'order_id' => $orderId,
                'points_delta' => $points,
                'balance_after' => max(0, $current + $points),
                'reason' => $reason,
            ]);
        });
    }

    public function reverse(int $userId, ?int $bookingId = null, ?int $orderId = null): void
    {
        DB::transaction(function () use ($userId, $bookingId, $orderId) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $points = (int) LoyaltyTransaction::where('user_id', $userId)
                ->when($bookingId, fn ($q) => $q->where('booking_id', $bookingId))
                ->when($orderId, fn ($q) => $q->where('order_id', $orderId))->sum('points_delta');
            if ($points !== 0) {
                LoyaltyTransaction::create(['user_id' => $userId, 'booking_id' => $bookingId,
                    'order_id' => $orderId, 'points_delta' => -$points,
                    'balance_after' => max(0, (int) LoyaltyTransaction::where('user_id', $userId)->sum('points_delta') - $points),
                    'reason' => 'sandbox_refund_reversal']);
            }
        });
    }

    public function balance(int $userId): int
    {
        return max(0, (int) LoyaltyTransaction::where('user_id', $userId)->sum('points_delta'));
    }

    public function redeem(User $user, int $points, \App\Models\ProductOrder $order): void
    {
        if ($points <= 0) {
            return;
        }
        DB::transaction(function () use ($user, $points, $order) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($points > $this->balance($user->id)) {
                throw new \InvalidArgumentException('Không đủ điểm để sử dụng.');
            }
            LoyaltyTransaction::create(['user_id' => $user->id, 'order_id' => $order->id, 'points_delta' => -$points,
                'balance_after' => $this->balance($user->id) - $points, 'reason' => 'order_redemption']);
        });
    }
}
