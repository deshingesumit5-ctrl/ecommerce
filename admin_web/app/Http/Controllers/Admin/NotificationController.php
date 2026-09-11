<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $unreadCount = Notification::where('is_read', false)->count();
        $notifications = Notification::latest()->take(10)->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        if ($request->filled('redirect') && $request->redirect) {
            return redirect($notification->url ?: route('admin.dashboard'));
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
