<?php
namespace App\Http\Controllers;

use App\Exceptions\AvailabilityConflictException;
use App\Http\Requests\StoreProductOrderRequest;
use App\Http\Requests\UpdateProductOrderStatusRequest;
use App\Models\ProductOrder;
use App\Services\ProductOrderService;
use InvalidArgumentException;

class ProductOrderController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $orders = ProductOrder::with('items.product')
            ->when($user->role === 'guest', fn($q) => $q->where('buyer_id', $user->id))
            ->when($user->role === 'seller', fn($q) => $q->whereHas('items.product', fn($p) => $p->where('seller_id', $user->id)))
            ->orderByDesc('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(StoreProductOrderRequest $request, ProductOrderService $service)
    {
        try {
            $validated = $request->validated();
            $order = $service->createOrder($request->user(), $validated['items']);
        } catch (AvailabilityConflictException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 409)
                : back()->withErrors(['order' => $exception->getMessage()])->withInput();
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['order' => $exception->getMessage()])->withInput();
        }

        return $request->expectsJson()
            ? response()->json(['data' => $order], 201)
            : redirect()->route('guest.orders.index')->with('status', 'Đã tạo đơn hàng: ' . $order->code);
    }

    public function updateStatus(UpdateProductOrderStatusRequest $request, ProductOrder $order, ProductOrderService $service)
    {
        try {
            $validated = $request->validated();
            $order = $service->transition($order, $request->user(), $validated['status']);
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['order' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $order])
            : back()->with('status', 'Đã cập nhật trạng thái đơn hàng.');
    }
}
