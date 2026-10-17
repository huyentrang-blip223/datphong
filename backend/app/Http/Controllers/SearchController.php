<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRoomsRequest;
use App\Services\BookingService;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index', ['rooms' => collect(), 'filters' => []]);
    }

    public function results(SearchRoomsRequest $request, BookingService $bookingService)
    {
        $filters = $request->validated();
        $rooms = app(\App\Services\RoomSearchService::class)->search($filters);

        if ($request->expectsJson()) {
            return response()->json($rooms);
        }

        return view('search.index', ['rooms' => $rooms, 'filters' => $filters]);
    }
}
