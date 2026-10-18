<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingLifecycleService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingLifecycleController extends Controller
{
    public function cancel(Request $request, Booking $booking, BookingLifecycleService $service)
    {
        return $this->perform($request, fn () => $service->cancel($booking, $request->user()));
    }

    public function complete(Request $request, Booking $booking, BookingLifecycleService $service)
    {
        return $this->perform($request, fn () => $service->complete($booking, $request->user()));
    }

    public function refund(Request $request, Booking $booking, BookingLifecycleService $service)
    {
        return $this->perform($request, fn () => $service->refund($booking, $request->user()));
    }

    private function perform(Request $request, callable $action)
    {
        try {
            $booking = $action();

            return $request->expectsJson() ? response()->json(['data' => $booking]) : back()->with('status', 'Đã cập nhật booking.');
        } catch (InvalidArgumentException $e) {
            return $request->expectsJson() ? response()->json(['message' => $e->getMessage()], 422) : back()->withErrors(['booking' => $e->getMessage()]);
        }
    }
}
