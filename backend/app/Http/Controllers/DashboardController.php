<?php
namespace App\Http\Controllers;

use App\Models\Homestay;
use App\Models\User;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('dashboard.admin', [
            'homestayCount' => Homestay::count(),
            'pendingCount' => Homestay::where('status', 'pending')->count(),
            'userCount' => User::count(),
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
