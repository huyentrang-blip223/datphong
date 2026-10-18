<?php

namespace App\Http\Controllers;

use App\Models\ExperienceBooking;
use App\Services\ExperienceBookingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExperienceBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = ExperienceBooking::with('experience.homestay', 'guest', 'payments')
            ->when($request->user()->role === 'guest', fn ($q) => $q->where('guest_id', $request->user()->id))
            ->when($request->user()->role === 'host', fn ($q) => $q->whereHas('experience.homestay', fn ($q) => $q->where('owner_id', $request->user()->id)))
            ->latest('id')->paginate(15);

        return view('experiences.bookings', compact('bookings'));
    }

    public function cancel(Request $request, ExperienceBooking $experienceBooking, ExperienceBookingService $service)
    {
        return $this->perform($request, fn () => $service->cancel($experienceBooking, $request->user()));
    }

    public function complete(Request $request, ExperienceBooking $experienceBooking, ExperienceBookingService $service)
    {
        return $this->perform($request, fn () => $service->complete($experienceBooking, $request->user()));
    }

    private function perform($request, $action)
    {
        try {
            $booking = $action();
        } catch (InvalidArgumentException $e) {
            return $request->expectsJson() ? response()->json(['message' => $e->getMessage()], 422) : back()->withErrors(['booking' => $e->getMessage()]);
        }

        return $request->expectsJson() ? response()->json(['data' => $booking]) : back()->with('status', 'Đã cập nhật đặt chỗ.');
    }
}
