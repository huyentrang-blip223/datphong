<?php
namespace App\Http\Controllers;

use App\Http\Requests\MockPaymentRequest;
use App\Models\ProductOrder;
use App\Services\CommercePaymentService;
use InvalidArgumentException;

class ProductOrderPaymentController extends Controller
{
    public function show(ProductOrder $order)
    {
        abort_unless((int) $order->buyer_id === (int) request()->user()->id, 403);

        $order->load([
            'items.product',
            'payments' => fn($query) => $query->latest('id'),
        ]);

        return view('payments.product_order', compact('order'));
    }

    public function store(MockPaymentRequest $request, ProductOrder $order, CommercePaymentService $service)
    {
        try {
            $payment = $service->payProductOrder($order, $request->user(), $request->validated('result'));
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], $payment->status === 'paid' ? 201 : 200)
            : back()->with('status', $payment->status === 'paid' ? 'Thanh toán đơn hàng thành công.' : 'Thanh toán đơn hàng thất bại.');
    }
}
