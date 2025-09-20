<?php
namespace App\Http\Controllers;

use App\Models\AssignedTask;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TaskNotification;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;

class AssignedTaskController extends Controller
{
    public function create(Request $request)
{
    try {
        $request->validate([
            'task_id' => 'required|integer|exists:task,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $existingAssignment = AssignedTask::where('task_id', $request->task_id)
                                          ->where('user_id', $request->user_id)
                                          ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error','This task is already assigned to the user.');
        }

        $assignedTask = new AssignedTask();
        $assignedTask->task_id = $request->task_id;
        $assignedTask->user_id = $request->user_id;
        $assignedTask->save();

        TaskAssignedNotification::create([
            'created_at' => now(),
            'task_id' => $request->task_id,
            'user_id' => $request->user_id,
            'notify_type' => 'NewAssignment'
        ]);

        return redirect()->back()->with('success','Task assigned successfully!');
    } catch (\Exception $e) {
        Log::error('Failed to create assigned task: ' . $e->getMessage());
        return redirect()->back()->with('error','Failed to assign task. Please try again.');
    }
}

public function showNotifications(Request $request)
{
    $tasknotifications = TaskNotification::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    if ($request->ajax()) {
        return view('partials.assigned_task__list', compact('taskCompletedNotifications'))->render();
    }

    return view('pages.assigned_task', compact('tasknotifications'));
}

public function destroyNotification($id)
{
    try {
        $notification = TaskNotification::findOrFail($id);
        $notification->delete();

        $tasknotifications = TaskNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Return the updated HTML list
        return redirect()->back()->with('success', 'Invitation deleted');
    } catch (\Exception $e) {
        Log::error('Failed to delete notification: ' . $e->getMessage());
        return redirect()->back()->with('error','Failed to delete notification. Please try again.');
    }
}



    public function destroy($user_id, $task_id){
    try{
        $assignedTask = AssignedTask::where('user_id', $user_id)
                                    ->where('task_id', $task_id)
                                    ->first();

        if(!$assignedTask){
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $assignedTask->delete();

        return redirect()->back()->with('success', 'Assignment deleted successfully.');
    } catch (\Exception $e) {
        Log::error('Failed to delete assigned task: ' . $e->getMessage());
        return redirect()->back()->with('error','Failed to delete assignment. Please try again.');
    }

    }
}