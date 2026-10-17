<?php
namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', request()->user()->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) request()->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return back()->with('status', 'Đã đánh dấu đã đọc.');
    }
}
