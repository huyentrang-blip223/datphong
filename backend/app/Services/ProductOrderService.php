<?php
namespace App\Services;

use App\Exceptions\AvailabilityConflictException;
use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductOrderService
{
    public function __construct(
        private LoyaltyService $loyaltyService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService
    ) {
    }

    public function createOrder(User $guest, array $items): ProductOrder
    {
        if ($guest->role !== 'guest') {
            throw new InvalidArgumentException('Chỉ guest được tạo đơn hàng.');
        }

        return DB::transaction(function () use ($guest, $items) {
            $ids = collect($items)->pluck('product_id')->unique()->values()->all();
            $products = LocalProduct::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;
            $homestayId = null;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];

                if (!$product || $product->status !== 'published') {
                    throw new InvalidArgumentException('Sản phẩm không hợp lệ.');
                }
                if ($product->stock_qty < $quantity) {
                    throw new AvailabilityConflictException('Sản phẩm không còn đủ tồn kho.');
                }

                $lineTotal = (float) $product->price * $quantity;
                $subtotal += $lineTotal;
                $homestayId ??= optional($product->homestays()->first())->id;
                $lines[] = [$product, $quantity, (float) $product->price, $lineTotal];
            }

            $order = ProductOrder::create([
                'code' => $this->newCode(),
                'buyer_id' => $guest->id,
                'homestay_id' => $homestayId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);

            foreach ($lines as [$product, $quantity, $unitPrice, $lineTotal]) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
                $product->decrement('stock_qty', $quantity);
                $this->notificationService->send((int) $product->seller_id, 'product_order_created', 'Có đơn hàng sản phẩm mới', 'Đơn ' . $order->code . ' có sản phẩm ' . $product->name . '.', 'product_order', $order->id);
            }

            $this->notificationService->send($guest, 'product_order_created', 'Đã tạo đơn hàng sản phẩm', 'Đơn hàng ' . $order->code . ' đã được tạo.', 'product_order', $order->id);
            $this->auditLogService->record($guest, 'product_order_created', 'product_orders', $order->id, null, [
                'total_amount' => $order->total_amount,
                'item_count' => count($lines),
            ]);

            return $order->fresh('items.product');
        });
    }

    public function transition(ProductOrder $order, User $actor, string $nextStatus): ProductOrder
    {
        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['packing', 'cancelled'],
            'packing' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($nextStatus, $allowed[$order->status] ?? [], true)) {
            throw new InvalidArgumentException('Chuyển trạng thái đơn hàng không hợp lệ.');
        }

        if ($actor->role === 'seller') {
            $sellerOwnsAnItem = $order->items()->whereHas('product', fn($q) => $q->where('seller_id', $actor->id))->exists();
            if (!$sellerOwnsAnItem) {
                throw new InvalidArgumentException('Seller không có quyền cập nhật đơn hàng này.');
            }
        } elseif ($actor->role !== 'admin') {
            throw new InvalidArgumentException('Không có quyền cập nhật đơn hàng.');
        }

        $fromStatus = $order->status;
        $order->update(['status' => $nextStatus]);

        if ($nextStatus === 'delivered') {
            $this->loyaltyService->award((int) $order->buyer_id, (int) floor((float) $order->total_amount / 10000), 'product_order_delivered', null, $order->id);
        }

        $this->notificationService->send((int) $order->buyer_id, 'product_order_status', 'Cập nhật đơn hàng sản phẩm', 'Đơn ' . $order->code . ' chuyển sang trạng thái ' . $nextStatus . '.', 'product_order', $order->id);
        $this->auditLogService->record($actor, 'product_order_status_' . $nextStatus, 'product_orders', $order->id, ['status' => $fromStatus], ['status' => $nextStatus]);

        return $order->fresh('items.product');
    }

    private function newCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (ProductOrder::where('code', $code)->exists());

        return $code;
    }
}
