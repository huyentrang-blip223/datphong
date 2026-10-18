<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashReceiptRequest;
use App\Models\Booking;
use App\Models\ExperienceBooking;
use App\Models\ProductOrder;
use App\Services\CashPaymentService;
use InvalidArgumentException;

class CashReceiptController extends Controller
{
    public function booking(CashReceiptRequest $request, Booking $booking, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->collect($booking, $request->user()));
    }

    public function order(CashReceiptRequest $request, ProductOrder $order, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->collect($order, $request->user()));
    }

    public function experience(CashReceiptRequest $request, ExperienceBooking $experienceBooking, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->collect($experienceBooking, $request->user()));
    }

    public function refundBooking(CashReceiptRequest $request, Booking $booking, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->refund($booking, $request->user()));
    }

    public function refundOrder(CashReceiptRequest $request, ProductOrder $order, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->refund($order, $request->user()));
    }

    public function refundExperience(CashReceiptRequest $request, ExperienceBooking $experienceBooking, CashPaymentService $service)
    {
        return $this->perform($request, fn () => $service->refund($experienceBooking, $request->user()));
    }

    private function perform(CashReceiptRequest $request, callable $action)
    {
        try {
            $payment = $action();
        } catch (InvalidArgumentException $e) {
            return $request->expectsJson() ? response()->json(['message' => $e->getMessage()], 422) : back()->withErrors(['payment' => $e->getMessage()]);
        }

        return $request->expectsJson() ? response()->json(['data' => $payment]) : back()->with('status', 'Đã ghi nhận xác nhận tiền mặt.');
    }
}
