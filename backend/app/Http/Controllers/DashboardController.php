<?php
namespace App\Http\Controllers;

use App\Models\Homestay;
use App\Models\Booking;
use App\Models\LocalProduct;
use App\Models\ProductOrder;
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
            'roomCount' => Room::whereHas('homestay', fn($q) => $q->where('owner_id', $user->id))->count(),
            'bookingCount' => Booking::whereHas('room.homestay', fn($q) => $q->where('owner_id', $user->id))->count(),
        ]);
    }

    public function seller()
    {
        $user = request()->user();

        return view('dashboard.seller', [
            'productCount' => LocalProduct::where('seller_id', $user->id)->count(),
            'publishedCount' => LocalProduct::where('seller_id', $user->id)->where('status', 'published')->count(),
            'lowStockCount' => LocalProduct::where('seller_id', $user->id)->where('stock_qty', '<=', 5)->count(),
            'orderCount' => ProductOrder::whereHas('items.product', fn($q) => $q->where('seller_id', $user->id))->count(),
        ]);
    }

    public function editOwnedHomestay(Homestay $homestay)
    {
        $this->authorize('update', $homestay);

        return view('dashboard.host_homestay_edit', compact('homestay'));
    }
}
