<?php
namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('guest', 'room.homestay', 'payments')
            ->whereHas('room.homestay', fn($q) => $q->where('owner_id', request()->user()->id))
            ->orderByDesc('id')
            ->paginate(15);

        return view('host.bookings.index', compact('bookings'));
    }
}
