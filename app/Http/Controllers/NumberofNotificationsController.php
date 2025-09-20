<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use Illuminate\Support\Facades\Auth;
use App\Models\ChangeRoleNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;

class NumberofNotificationsController extends Controller
{
    public function count()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0], 200);
        }
    
        $user = Auth::user();
    
        $invitationsCount = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', false)
            ->count();
    
        $responsesCount = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', true)
            ->count();
        
        $changeRoleCount = ChangeRoleNotification::where('receiver_id', $user->id)
            ->count();

        $assignedTasksCount = TaskAssignedNotification::where('user_id', $user->id)
            ->where('notify_type', 'NewAssignment')
            ->count();

        $completedTasksCount =  TaskCompletedNotification::where('user_id', $user->id)
        ->where('notify_type', 'TaskCompleted')
        ->count();

        $totalCount = $invitationsCount + $responsesCount + $changeRoleCount+ $assignedTasksCount + $completedTasksCount;
    
        return response()->json(['count' => $totalCount], 200);
    }
    
    public function countInvitations()
    {
        if (!Auth::check()) {
            return response()->json(['countInvitations' => 0], 200);
        }
    
        $user = Auth::user();
    
        $invitationsCount1 = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', false)
            ->count();
    
        $responsesCount = ProjectInvitation::where('receiver_id', $user->id)
            ->where('response', true)
            ->count();
        
        
        $invitationsCount = $invitationsCount1 + $responsesCount ;
    
        return response()->json(['countInvitations' => $invitationsCount], 200);
    }

    public function countChangeRole()
    {
        if (!Auth::check()) {
            return response()->json(['countChangeRole' => 0], 200);
        }
    
        $user = Auth::user();
    
        $changeRoleCount = ChangeRoleNotification::where('receiver_id', $user->id)
            ->count();
    
        return response()->json(['countChangeRole' => $changeRoleCount], 200);
    }

    public function countAssignedTasks(): mixed
    {
        if (!Auth::check()) {
            return response()->json(['assignTaskCount' => 0], 200);
        }
    
        $user = Auth::user();
    
        $assignedTasksCount = TaskAssignedNotification::where('user_id', $user->id)
            ->where('notify_type', 'NewAssignment')
            ->count();
    
        return response()->json(['countAssignedTasks' => $assignedTasksCount], 200);
    }

    public function countCompletedTasks()
    {
        if(!Auth::check()) {
            return response()->json(['completedTaskCount' => 0], 200);
        }

        $user = Auth::user();

        $completedTasksCount = TaskCompletedNotification::where('user_id', $user->id)
            ->where('notify_type', 'TaskCompleted')
            ->count();

        return response()->json(['completedTaskCount' => $completedTasksCount], 200);
    }
}
