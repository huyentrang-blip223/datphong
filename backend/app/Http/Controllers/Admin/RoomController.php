<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Homestay;
use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('homestay')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $room = new Room();
        $homestays = Homestay::orderBy('name')->get();

        return view('admin.rooms.create', compact('room', 'homestays'));
    }

    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('rooms', 'public');
        }

        Room::create($data);

        return redirect()->route('admin.rooms.index')->with('status', 'Đã tạo phòng.');
    }

    public function edit(Room $room)
    {
        $homestays = Homestay::orderBy('name')->get();

        return view('admin.rooms.edit', compact('room', 'homestays'));
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }

            $data['image_path'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($data);

        return redirect()->route('admin.rooms.index')->with('status', 'Đã cập nhật phòng.');
    }

    public function destroy(Room $room)
    {
        try {
            $imagePath = $room->image_path;
            $room->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        } catch (QueryException $e) {
            return back()->withErrors('Không thể xóa phòng đã có dữ liệu booking/lịch liên quan. Hãy tạm ẩn phòng thay vì xóa.');
        }

        return redirect()->route('admin.rooms.index')->with('status', 'Đã xóa phòng.');
    }
}
