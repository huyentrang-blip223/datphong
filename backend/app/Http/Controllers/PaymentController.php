<?php
namespace App\Http\Controllers;

use App\Http\Requests\MockPaymentRequest;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\VnPayPaymentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function show(Booking $booking)
    {
        abort_unless((int) $booking->guest_id === (int) request()->user()->id, 403);

        $booking->load([
            'room.homestay',
            'payments' => fn($query) => $query->latest('id'),
        ]);

        return view('payments.show', compact('booking'));
    }

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

    public function createVnPay(Request $request, Booking $booking, VnPayPaymentService $service)
    {
        try {
            $paymentUrl = $service->createPaymentUrl($booking, $request->user(), $request->ip());
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['payment_url' => $paymentUrl])
            : redirect()->away($paymentUrl);
    }

    public function vnPayCallback(Request $request, VnPayPaymentService $service)
    {
        try {
            $result = $service->handleReturn($request->query());
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'booking_code' => $result['booking']->code,
                'payment_status' => $result['payment']->status,
            ]);
        }

        return redirect()->route('guest.payments.show', $result['booking'])->with(
            'status',
            $result['success'] ? 'Thanh toán VNPay thành công.' : 'Thanh toán VNPay thất bại.'
        );
    }
}
