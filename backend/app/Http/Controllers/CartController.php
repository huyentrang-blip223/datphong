<?php

namespace App\Http\Controllers;

use App\Exceptions\AvailabilityConflictException;
use App\Http\Requests\CartCheckoutRequest;
use App\Http\Requests\CartItemRequest;
use App\Services\CartService;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CartController extends Controller
{
    public function index(Request $request, CartService $service)
    {
        return view('cart.index', ['lines' => $service->summary($request->session()), 'checkoutKey' => $service->key($request->session()), 'balance' => app(LoyaltyService::class)->balance($request->user()->id)]);
    }

    public function store(CartItemRequest $request, CartService $service)
    {
        return $this->change($request, $service, true);
    }

    public function update(CartItemRequest $request, CartService $service)
    {
        return $this->change($request, $service, false);
    }

    private function change($request, $service, $add)
    {
        try {
            $service->change($request->session(), (int) $request->product_id, (int) $request->quantity, $add);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        return redirect()->route('guest.cart.index')->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function checkout(CartCheckoutRequest $request, CartService $service)
    {
        try {
            $orders = $service->checkout($request->user(), $request->session(), $request->checkout_key, (int) ($request->points ?? 0));
        } catch (AvailabilityConflictException|InvalidArgumentException $e) {
            return $request->expectsJson() ? response()->json(['message' => $e->getMessage()], $e instanceof AvailabilityConflictException ? 409 : 422) : back()->withErrors(['cart' => $e->getMessage()]);
        }

        return $request->expectsJson() ? response()->json(['data' => $orders], 201) : redirect()->route('guest.orders.index')->with('status', 'Đã tạo '.count($orders).' đơn theo người bán. Chọn từng đơn để thanh toán.');
    }
}
