<?php
namespace App\Http\Controllers;

use App\Http\Requests\MockPaymentRequest;
use App\Models\Booking;
use App\Services\PaymentService;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function store(MockPaymentRequest $request, Booking $booking, PaymentService $service)
    {
        try {
            $payment = $service->payTest($booking, $request->user(), $request->validated('result'));
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], $payment->status === 'paid' ? 201 : 200)
            : back()->with('status', $payment->status === 'paid' ? 'Thanh toán test thành công.' : 'Thanh toán test thất bại.');
    }
}
