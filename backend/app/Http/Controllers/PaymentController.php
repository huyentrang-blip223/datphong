<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashPaymentRequest;
use App\Models\Booking;
use App\Services\CashPaymentService;
use App\Services\VnPayPaymentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function vnPayIpn(Request $request, VnPayPaymentService $service)
    {
        try {
            $result = $service->handleReturn($request->query());

            return response()->json(['RspCode' => ($result['duplicate'] ?? false) ? '02' : '00', 'Message' => 'Confirm Success']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['RspCode' => in_array($e->getCode(), [4, 97], true) ? str_pad((string) $e->getCode(), 2, '0', STR_PAD_LEFT) : '99', 'Message' => 'Invalid signature, amount or state']);
        } catch (\Throwable $e) {
            return response()->json(['RspCode' => '99', 'Message' => 'Retry later']);
        }
    }

    public function show(Booking $booking)
    {
        abort_unless((int) $booking->guest_id === (int) request()->user()->id, 403);
        app(\App\Services\BookingLifecycleService::class)->expireDue($booking->room_id);
        $booking->refresh();

        $booking->load([
            'room.homestay',
            'payments' => fn ($query) => $query->latest('id'),
        ]);

        return view('payments.show', compact('booking'));
    }

    public function store(CashPaymentRequest $request, Booking $booking, CashPaymentService $service)
    {
        try {
            $payment = $service->select($booking, $request->user());
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], 201)
            : back()->with('status', 'Đã chọn thanh toán tiền mặt. Vui lòng thanh toán trực tiếp cho chủ homestay.');
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
            $payload = [
                'success' => $result['success'],
                'payment_status' => $result['payment']->status,
            ];
            if (isset($result['product_order'])) {
                $payload['order_code'] = $result['product_order']->code;
            } else {
                $payload['booking_code'] = $result['booking']->code;
            }

            return response()->json($payload);
        }

        if (isset($result['product_order'])) {
            return redirect()->route('guest.order-payments.show', $result['product_order'])->with(
                'status',
                $result['success'] ? 'Thanh toán VNPay thành công.' : 'Thanh toán VNPay thất bại.'
            );
        }

        return redirect()->route('guest.payments.show', $result['booking'])->with(
            'status',
            $result['success'] ? 'Thanh toán VNPay thành công.' : 'Thanh toán VNPay thất bại.'
        );
    }
}
