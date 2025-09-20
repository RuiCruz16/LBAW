<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\TaskCompletedNotification;

class TaskController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if ($user && $user->user_status === 'Blocked') {
                Auth::logout();
                return redirect('/login')->with('error', 'Your account has been blocked.');
            }

            return $next($request);
        });
    }

    public function show($id)
    {
        try {
            $task = Task::with(['status', 'comments.user', 'users', 'project.users'])->findOrFail($id);
            $statuses = TaskStatus::all();

            $user = Auth::user();

            if (!$task->project->users->contains($user->id) && !$user->isAdmin()) {
                return request()->expectsJson()
                    ? response()->json(['error' => 'Access denied'], 403)
                    : redirect()->back()->with('error', 'Access denied.');
            }

            $this->authorize('view', $task);

            if (request()->expectsJson()) {
                return response()->json([
                    'task' => $task,
                    'statuses' => $statuses,
                ]);
            }

            return view('tasks.show', [
                'task' => $task,
                'statuses' => $statuses,
            ]);
        } catch (\Exception $e) {
            return request()->expectsJson()
                ? response()->json(['error' => 'Failed to display the task'], 500)
                : redirect()->back()->with('error', 'Failed to display the task.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);

            $this->authorize('update', $task);

            $validated = $request->validate([
                'task_title' => 'required|string|max:255',
                'task_description' => 'nullable|string',
                'deadline' => 'nullable|date',
                'task_status_id' => 'required|integer|exists:task_status,id',
                'task_priority' => 'nullable|string|in:Low,Medium,High',
            ]);

            $task->update($validated);
            $task = Task::findOrFail($id);
            $project = $task->project; 
            foreach ($project->users as $user) {
                if ($user->id !== Auth::id()) {
                    if ($task->task_status_id == 3) {
                        TaskCompletedNotification::create([
                            'created_at' => now(),
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'notify_type' => 'TaskCompleted',
                            'task_assigned_message' => "Task '{$task->task_title}' in '{$task->project->project_name}' has been completed."
                        ]);
                    }
                }
            }

            redirect()->back()->with('success', 'Task updated successfully.');
            return response()->json([
                'message' => 'Task updated successfully!',
                'task' => $task
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            redirect()->back()->with('error', 'Failed to update task. Please try again.');
            return response()->json([
                'message' => 'An error occurred while updating the task.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $this->authorize('delete', $task);

            $task->comments()->delete();
            $task->notifications()->delete();
            $task->assignedUsers()->detach();

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task. Please try again.'
            ], 500);
        }
    }

    public function store(Request $request, $projectId)
    {
        $validatedData = $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_status_id' => 'required|integer|exists:task_status,id',
            'deadline' => 'nullable|date',
        ]);

        try {
            $task = new Task();
            $task->task_title = $validatedData['task_title'];
            $task->task_description = $validatedData['task_description'];
            $task->task_status_id = $validatedData['task_status_id'];
            $task->deadline = $validatedData['deadline'];
            $task->project_id = $projectId;
            $task->created_at = now();

            $task->save();

            $project = $task->project;
            foreach ($project->users as $user) {
                if ($user->id !== Auth::id()) {
                    if ($task->task_status_id == 3) {
                        TaskCompletedNotification::create([
                            'created_at' => now(),
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'notify_type' => 'TaskCompleted'
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', 'Task added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add task. Please try again.');
        }
    }
}