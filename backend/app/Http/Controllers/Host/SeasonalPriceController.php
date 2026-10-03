<?php
namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeasonalPriceRequest;
use App\Models\Room;
use App\Models\SeasonalPrice;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class SeasonalPriceController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('homestay')
            ->whereHas('homestay', fn($q) => $q->where('owner_id', $request->user()->id))
            ->orderBy('name')
            ->get();
        $room = $rooms->firstWhere('id', (int) $request->query('room_id')) ?: $rooms->first();
        $seasonalPrices = $room ? $room->seasonalPrices()->orderByDesc('priority')->orderBy('start_date')->get() : collect();

        return view('host.seasonal_prices.index', compact('rooms', 'room', 'seasonalPrices'));
    }

    public function store(StoreSeasonalPriceRequest $request, Room $room, AvailabilityService $service)
    {
        $service->createSeasonalPrice($room, $request->validated());

        return redirect()->route('host.seasonal-prices.index', ['room_id' => $room->id])->with('status', 'Đã thêm giá mùa.');
    }

    public function destroy(SeasonalPrice $seasonalPrice)
    {
        $this->authorize('update', $seasonalPrice->room);
        $roomId = $seasonalPrice->room_id;
        $seasonalPrice->delete();

        return redirect()->route('host.seasonal-prices.index', ['room_id' => $roomId])->with('status', 'Đã xóa giá mùa.');
    }
}
