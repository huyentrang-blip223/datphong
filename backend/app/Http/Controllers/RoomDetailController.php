<?php
namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;

class RoomDetailController extends Controller
{
    public function show(Request $request, Room $room, BookingService $bookingService)
    {
        $room->load([
            'homestay.owner',
            'homestay.rooms' => function ($query) {
                $query->where('active', true);
            },
            'homestay.localProducts',
            'homestay.experiences',
            'seasonalPrices',
        ]);

        $checkin = $request->query('checkin', date('Y-m-d', strtotime('+1 day')));
        $checkout = $request->query('checkout', date('Y-m-d', strtotime('+2 days')));
        $guests = (int) $request->query('guests', min(2, $room->max_guests));

        $quote = null;
        try {
            $quote = $bookingService->quote($room, $checkin, $checkout, $guests);
        } catch (\Exception $e) {
            // Fallback khi ngày hoặc sức chứa chưa hợp lệ
        }

        return view('rooms.show', compact('room', 'checkin', 'checkout', 'guests', 'quote'));
    }
}
