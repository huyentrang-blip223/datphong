<?php

namespace App\Services;

use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartService
{
    public function items(Session $session): array
    {
        return $session->get('cart.items', []);
    }

    public function key(Session $session): string
    {
        if (! $session->has('cart.key')) {
            $session->put('cart.key', (string) Str::uuid());
        }

        return $session->get('cart.key');
    }

    public function change(Session $session, int $id, int $quantity, bool $add = false): void
    {
        $items = $this->items($session);
        $quantity += $add ? (int) ($items[$id] ?? 0) : 0;
        if ($quantity > 0) {
            $product = LocalProduct::findOrFail($id);
            if ($product->status !== 'published' || $quantity > min(1000, $product->stock_qty)) {
                throw new InvalidArgumentException('Sản phẩm không còn đủ số lượng.');
            }
            $items[$id] = $quantity;
        } else {
            unset($items[$id]);
        }
        if (count($items) > 20) {
            throw new InvalidArgumentException('Giỏ hàng tối đa 20 sản phẩm.');
        }
        $session->put('cart.items', $items);
        $session->put('cart.key', (string) Str::uuid());
    }

    public function summary(Session $session): array
    {
        $items = $this->items($session);

        return LocalProduct::with('seller')->whereIn('id', array_keys($items))->orderBy('seller_id')->get()->map(fn ($p) => [
            'product' => $p, 'quantity' => (int) $items[$p->id], 'line_total' => round((float) $p->price * $items[$p->id], 2),
        ])->all();
    }

    public function checkout(User $guest, Session $session, string $key, int $points): array
    {
        $orders = DB::transaction(function () use ($guest, $session, $key, $points) {
            User::whereKey($guest->id)->lockForUpdate()->firstOrFail();
            $previous = DB::table('cart_checkouts')->where('checkout_key', $key)->first();
            if ($previous) {
                abort_unless((int) $previous->user_id === (int) $guest->id, 403);

                return ProductOrder::whereIn('id', json_decode($previous->order_ids, true))->get()->all();
            }
            if (! hash_equals($this->key($session), $key)) {
                throw new InvalidArgumentException('Giỏ hàng đã thay đổi; vui lòng tải lại trang.');
            }
            $items = $this->items($session);
            if (! $items) {
                throw new InvalidArgumentException('Giỏ hàng đang trống.');
            }
            $products = LocalProduct::whereIn('id', array_keys($items))->orderBy('id')->lockForUpdate()->get();
            if ($products->count() !== count($items)) {
                throw new InvalidArgumentException('Có sản phẩm không còn tồn tại.');
            }
            $groups = $products->groupBy('seller_id')->sortKeys();
            $capacity = $groups->map(fn ($group) => (int) floor($group->sum(fn ($p) => (float) $p->price * $items[$p->id]) * config('loyalty.maximum_discount_percent') / 100 / config('loyalty.redemption_value')));
            if ($points > $capacity->sum() || $points > app(LoyaltyService::class)->balance($guest->id)) {
                throw new InvalidArgumentException('Điểm vượt số dư hoặc giới hạn giảm giá 50%.');
            }
            $orders = [];
            foreach ($groups as $sellerId => $group) {
                $used = min($points, $capacity[$sellerId]);
                $orders[] = app(ProductOrderService::class)->createOrder($guest, $group->map(fn ($p) => ['product_id' => $p->id, 'quantity' => (int) $items[$p->id]])->all(), $used);
                $points -= $used;
            }
            DB::table('cart_checkouts')->insert(['user_id' => $guest->id, 'checkout_key' => $key, 'order_ids' => json_encode(array_map(fn ($o) => $o->id, $orders)), 'created_at' => now()]);

            return $orders;
        }, 3);
        if ($session->get('cart.key') === $key) {
            $session->forget('cart');
        }

        return $orders;
    }
}
