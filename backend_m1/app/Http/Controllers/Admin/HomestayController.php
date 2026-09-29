<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHomestayRequest;
use App\Http\Requests\UpdateHomestayRequest;
use App\Models\Homestay;
use App\Models\User;

class HomestayController extends Controller
{
    public function index()
    {
        $items = Homestay::with('owner')
            ->when(request('status'), fn($q, $v) => $q->where('status', $v))
            ->when(request('q'), fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderByDesc('id')->paginate(10)->withQueryString();
        return view('admin.homestays.index', compact('items'));
    }

    public function create()
    {
        $hosts = User::where('role', 'host')->where('status', 'active')->orderBy('full_name')->get();
        return view('admin.homestays.create', compact('hosts'));
    }

    public function store(StoreHomestayRequest $request)
    {
        Homestay::create($request->validated());
        return redirect()->route('admin.homestays.index')->with('status', 'Đã tạo homestay.');
    }

    public function edit(Homestay $homestay)
    {
        $hosts = User::where('role', 'host')->where('status', 'active')->orderBy('full_name')->get();
        return view('admin.homestays.edit', compact('homestay', 'hosts'));
    }

    public function update(UpdateHomestayRequest $request, Homestay $homestay)
    {
        $homestay->update($request->validated());
        return redirect()->route('admin.homestays.index')->with('status', 'Đã cập nhật homestay.');
    }

    public function destroy(Homestay $homestay)
    {
        if ($homestay->status === 'approved') {
            return back()->withErrors(['delete' => 'Không xóa trực tiếp homestay đã duyệt; hãy chuyển trạng thái suspended.']);
        }
        $homestay->delete();
        return back()->with('status', 'Đã xóa homestay.');
    }
}
