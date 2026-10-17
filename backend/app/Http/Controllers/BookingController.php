<?php

namespace App\Http\Controllers;

use App\Exceptions\AvailabilityConflictException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function index()
    {
        app(\App\Services\BookingLifecycleService::class)->expireDue();
        $bookings = Booking::with('room.homestay', 'payments', 'reviews')
            ->where('guest_id', request()->user()->id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService)
    {
        $data = $request->validated();
        $room = Room::with('homestay')->findOrFail($data['room_id']);

        try {
            $booking = $bookingService->createBooking(
                $request->user(),
                $room,
                $data['checkin'],
                $data['checkout'],
                (int) $data['guests']
            );
        } catch (AvailabilityConflictException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return back()->withErrors(['availability' => $exception->getMessage()])->withInput();
        } catch (InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['booking' => $exception->getMessage()])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $booking->fresh('statusLogs')], 201);
        }

        return redirect()
            ->route('guest.payments.show', $booking)
            ->with('status', 'Đặt phòng thành công: '.$booking->code.'. Vui lòng kiểm tra và thanh toán.');
    }
}
