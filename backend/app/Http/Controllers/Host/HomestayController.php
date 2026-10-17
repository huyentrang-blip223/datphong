<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveOwnedHomestayRequest;
use App\Models\Homestay;
use App\Models\HomestayVerification;
use App\Services\HomestaySubmissionService;
use InvalidArgumentException;

class HomestayController extends Controller
{
    public function index()
    {
        $homestays = Homestay::where('owner_id', request()->user()->id)->latest('id')->paginate(12);

        return view('host.homestays.index', compact('homestays'));
    }

    public function create()
    {
        return view('host.homestays.form', ['homestay' => new Homestay()]);
    }

    public function edit(Homestay $homestay)
    {
        $this->authorize('update', $homestay);

        return view('host.homestays.form', ['homestay' => $homestay,
            'verifications' => HomestayVerification::where('homestay_id', $homestay->id)->latest('id')->get()]);
    }

    public function store(SaveOwnedHomestayRequest $request, HomestaySubmissionService $service)
    {
        return $this->save($request, $service);
    }

    public function update(SaveOwnedHomestayRequest $request, HomestaySubmissionService $service, Homestay $homestay)
    {
        return $this->save($request, $service, $homestay);
    }

    private function save($request, $service, $homestay = null)
    {
        try {
            $homestay = $service->save($request->user(), $request->validated(), $homestay);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['homestay' => $e->getMessage()])->withInput();
        }

        return redirect()->route('host.homestays.edit', $homestay)->with('status', 'Đã lưu và gửi hồ sơ chờ duyệt.');
    }
}
