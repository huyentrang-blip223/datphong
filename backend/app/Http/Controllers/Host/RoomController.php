<?php
namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Homestay;
use App\Models\Room;

class RoomController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $rooms = Room::with('homestay')
            ->whereHas('homestay', fn($q) => $q->where('owner_id', $user->id))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('host.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $homestays = Homestay::where('owner_id', request()->user()->id)->orderBy('name')->get();

        return view('host.rooms.create', compact('homestays'));
    }

    public function store(StoreRoomRequest $request)
    {
        Room::create($request->validated());

        return redirect()->route('host.rooms.index')->with('status', 'Đã tạo phòng.');
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room);

        $homestays = Homestay::where('owner_id', request()->user()->id)->orderBy('name')->get();

        return view('host.rooms.edit', compact('room', 'homestays'));
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $this->authorize('update', $room);
        $room->update($request->validated());

        return redirect()->route('host.rooms.index')->with('status', 'Đã cập nhật phòng.');
    }
}
