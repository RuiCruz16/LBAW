<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Models\ChangeRoleNotification;
use App\Models\ProjectInvitation;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function index()
    {
        return view('pages.notifications');
    }
    public function showTaskCompletedNotifications(Request $request)
{
    $taskCompletedNotifications = TaskCompletedNotification::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    if ($request->ajax()) {
        return view('partials.task_completed_list', compact('taskCompletedNotifications'))->render();
    }

    return view('pages.task_completed', compact('taskCompletedNotifications'));
}

public function destroyTaskCompletedNotification($id)
{
    try {
        $notification = TaskCompletedNotification::findOrFail($id);
        $notification->delete();

        $tasknotifications = TaskCompletedNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Return the updated HTML list
        return redirect()->back()->with('success', 'Invitation deleted');
    } catch (\Exception $e) {
        Log::error('Failed to delete notification: ' . $e->getMessage());
        return redirect()->back()->with('error','Failed to delete notification. Please try again.');
    }
}

public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        // Delete all notifications for the user
        TaskCompletedNotification::where('user_id', $user->id)->delete();
        TaskAssignedNotification::where('user_id', $user->id)->delete();
        ChangeRoleNotification::where('receiver_id', $user->id)->delete();
        ProjectInvitation::where('receiver_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }
}