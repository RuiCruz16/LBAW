<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Support\Facades\Auth;

class TaskCompletedNotificationsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $taskCompletedNotifications = TaskCompletedNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.task_completed', compact('taskCompletedNotifications'));
    }
}