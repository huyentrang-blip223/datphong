<?php
namespace App\Http\Controllers;

use App\Http\Requests\SearchRoomsRequest;
use App\Models\Room;
use App\Services\BookingService;
use InvalidArgumentException;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index', ['rooms' => collect(), 'filters' => []]);
    }

    public function results(SearchRoomsRequest $request, BookingService $bookingService)
    {
        $filters = $request->validated();
        $query = Room::with('homestay')
            ->where('active', true)
            ->where('max_guests', '>=', $filters['guests'])
            ->whereHas('homestay', function ($q) use ($filters) {
                $q->where('status', 'approved');
                if (!empty($filters['location'])) {
                    $q->where(function ($locationQuery) use ($filters) {
                        $locationQuery->where('province', 'like', '%' . $filters['location'] . '%')
                            ->orWhere('district', 'like', '%' . $filters['location'] . '%');
                    });
                }
            })
            ->orderBy('base_price')
            ->get();

        $rooms = $query->filter(function (Room $room) use ($bookingService, $filters) {
            try {
                $quote = $bookingService->quote($room, $filters['checkin'], $filters['checkout'], (int) $filters['guests']);
            } catch (InvalidArgumentException $exception) {
                return false;
            }

            $available = collect($quote['nightly'])->every(fn($night) => $night['status'] === 'open' && $night['available_units'] >= 1);
            $priceOk = (!isset($filters['min_price']) || $quote['average_unit_price'] >= (float) $filters['min_price'])
                && (!isset($filters['max_price']) || $quote['average_unit_price'] <= (float) $filters['max_price']);

            if ($available && $priceOk) {
                $room->quote = $quote;
                return true;
            }

            return false;
        })->values();

        if ($request->expectsJson()) {
            return response()->json(['data' => $rooms]);
        }

        return view('search.index', ['rooms' => $rooms, 'filters' => $filters]);
    }
}
