<?php
namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Models\Room;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('homestay')
            ->whereHas('homestay', fn($q) => $q->where('owner_id', $request->user()->id))
            ->orderBy('name')
            ->get();
        $room = $rooms->firstWhere('id', (int) $request->query('room_id')) ?: $rooms->first();
        $availability = collect();

        if ($room) {
            $from = $request->query('from', now()->toDateString());
            $to = $request->query('to', now()->addDays(14)->toDateString());
            $availability = $room->availability()->whereBetween('stay_date', [$from, $to])->orderBy('stay_date')->get();
        }

        return view('host.availability.index', compact('rooms', 'room', 'availability'));
    }

    public function update(UpdateAvailabilityRequest $request, Room $room, AvailabilityService $service)
    {
        try {
            $service->updateRange($room, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['availability' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('host.availability.index', ['room_id' => $room->id])->with('status', 'Đã cập nhật lịch phòng.');
    }
}
