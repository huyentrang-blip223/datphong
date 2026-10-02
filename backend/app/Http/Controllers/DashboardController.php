<?php
namespace App\Http\Controllers;

use App\Models\Homestay;
use App\Models\Booking;
use App\Models\LocalProduct;
use App\Models\Room;
use App\Models\User;
use App\Services\PythonDataService;

class DashboardController extends Controller
{
    public function admin(PythonDataService $pythonDataService)
    {
        return view('dashboard.admin', [
            'homestayCount' => Homestay::count(),
            'pendingCount' => Homestay::where('status', 'pending')->count(),
            'userCount' => User::count(),
            'roomCount' => Room::count(),
            'bookingCount' => Booking::count(),
            'localProductCount' => LocalProduct::where('status', 'published')->count(),
            'seasonality' => $pythonDataService->seasonality(),
            'topHomestays' => $pythonDataService->topHomestays(10),
        ]);
    }

    public function host()
    {
        $user = request()->user();
        return view('dashboard.host', [
            'homestayCount' => Homestay::where('owner_id', $user->id)->count(),
            'pendingCount' => Homestay::where('owner_id', $user->id)->where('status', 'pending')->count(),
        ]);
    }

    public function editOwnedHomestay(Homestay $homestay)
    {
        $this->authorize('update', $homestay);

        return view('dashboard.host_homestay_edit', compact('homestay'));
    }
}
