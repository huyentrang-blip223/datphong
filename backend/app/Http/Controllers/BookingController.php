<?php
namespace App\Http\Controllers;

use App\Exceptions\AvailabilityConflictException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Room;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\RoomAvailability;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function index()
    {
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
            ->with('status', 'Đặt phòng thành công: ' . $booking->code . '. Vui lòng kiểm tra và thanh toán.');
    }

    public function cancel(Booking $booking, NotificationService $notificationService)
    {
        abort_unless((int) $booking->guest_id === (int) request()->user()->id, 403);

        if (!in_array($booking->status, ['holding', 'pending_payment', 'confirmed'], true)) {
            return back()->withErrors(['booking' => 'Trạng thái booking không cho phép hủy.']);
        }

        DB::transaction(function () use ($booking, $notificationService) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;
            $locked->update(['status' => 'cancelled', 'cancel_reason' => 'guest_cancelled']);

            $dates = [];
            for ($date = $locked->checkin_date->copy(); $date->lt($locked->checkout_date); $date->addDay()) {
                $dates[] = $date->toDateString();
            }

            RoomAvailability::where('room_id', $locked->room_id)
                ->whereIn('stay_date', $dates)
                ->lockForUpdate()
                ->get()
                ->each(function (RoomAvailability $row) {
                    if ($row->units_sold > 0) {
                        $row->decrement('units_sold');
                    }
                });

            BookingStatusLog::create([
                'booking_id' => $locked->id,
                'from_status' => $from,
                'to_status' => 'cancelled',
                'actor_id' => request()->user()->id,
                'reason' => 'guest_cancelled',
            ]);

            $locked->load('room.homestay', 'guest');
            $notificationService->send($locked->guest, 'booking_cancelled', 'Đã hủy booking', 'Booking ' . $locked->code . ' đã được hủy.', 'booking', $locked->id);

            if ($locked->room?->homestay?->owner_id) {
                $notificationService->send((int) $locked->room->homestay->owner_id, 'booking_cancelled', 'Khách đã hủy booking', 'Booking ' . $locked->code . ' của phòng ' . $locked->room->name . ' đã được khách hủy.', 'booking', $locked->id);
            }
        });

        return back()->with('status', 'Đã hủy booking.');
    }
}
