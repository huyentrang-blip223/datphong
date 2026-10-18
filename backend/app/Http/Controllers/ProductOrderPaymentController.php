<?php
namespace App\Http\Controllers;

use App\Http\Requests\CashPaymentRequest;
use App\Models\ProductOrder;
use App\Services\CashPaymentService;
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

    public function store(CashPaymentRequest $request, ProductOrder $order, CashPaymentService $service)
    {
        try {
            $payment = $service->select($order, $request->user());
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], 201)
            : back()->with('status', 'Đã chọn thanh toán tiền mặt khi nhận hàng. Chưa ghi nhận đã thu tiền.');
    }
}
