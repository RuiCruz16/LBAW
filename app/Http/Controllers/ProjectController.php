<?php

namespace App\Http\Controllers;

use App\Models\ProjectRole;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ChangeRoleNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;

class ProjectController extends Controller
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

    public function list(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect('/login');
            }

            $projects = Project::where('creator_id', Auth::id())
                ->with('tasks')
                ->orderBy('created_at', 'desc')
                ->get();

            $sharedProjects = Project::whereHas('roles', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->where('creator_id', '!=', Auth::id())
                ->with('tasks')
                ->orderBy('created_at', 'desc')
                ->get();

            $tasksDue = Task::with('project')
                ->whereIn('project_id', $projects->pluck('id')->merge($sharedProjects->pluck('id')))
                ->whereBetween('deadline', [now(), now()->addDays(7)])
                ->orderBy('deadline', 'asc')
                ->paginate(5);

            return view('pages.projects', compact('projects', 'sharedProjects', 'tasksDue'));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return redirect('/')->with('error', 'An error occurred. Please try again.');
        }
    }

    public function create(Request $request)
    {
        try {
            $project = new Project();

            $project->project_name = $request->input('projectname');
            $project->project_description = $request->input('description');
            $project->creator_id = Auth::id();
            $project->project_status = 'Active';

            if ($project->save()) {
                $role = new ProjectRole();
                $role->user_id = Auth::id();
                $role->project_id = $project->id;
                $role->user_role = 'ProjectCreator';

                if ($role->save()) {
                    return redirect()->route('projects')->with('success', 'Project created successfully!');
                } else {
                    Log::error('Failed to save project role.');
                    return redirect()->route('projects')->with('error', 'Failed to assign project role.');
                }
            } else {
                Log::error('Failed to save project.');
                return redirect()->route('projects')->with('error', 'Failed to create project.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to create project: ' . $e->getMessage());
            return redirect()->route('projects')->with('error', 'Failed to create project. Please try again.');
        }
    }

    public function delete($id)
    {
        try {
            $project = Project::find($id);

            $this->authorize('delete', $project);

            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete project. Please try again.']);
        }
    }

    public function showCreateForm()
    {
        return view('pages.add_project');
    }

    public function show($id)
    {
        try {
            $project = Project::with(['tasks.status'])->findOrFail($id);

            $this->authorize('view', $project);

            $defaultStatuses = [
                1 => ['id' => 1, 'task_status_name' => 'To Do'],
                2 => ['id' => 2, 'task_status_name' => 'In Progress'],
                3 => ['id' => 3, 'task_status_name' => 'Completed'],
            ];

            $defaultStatusesCollection = collect($defaultStatuses);

            $databaseStatuses = TaskStatus::all()->mapWithKeys(function ($status) {
                return [$status->id => [
                    'id' => $status->id,
                    'task_status_name' => $status->task_status_name,
                ]];
            });

            $statuses = $defaultStatusesCollection->keyBy('id')->union($databaseStatuses);

            $tasksGroupedByStatus = $statuses->mapWithKeys(function ($status) use ($project) {
                return [$status['id'] => $project->tasks->where('task_status_id', $status['id'])];
            });

            return view('pages.view_project', compact('project', 'tasksGroupedByStatus', 'statuses'));
        } catch (\Exception $e) {
            Log::error('Failed to view project: ' . $e->getMessage());
            return redirect()->route('projects')->with('error', 'Failed to view project. Please try again.');
        }
    }

    public function addMember(Request $request, $projectId)
    {
        try {
            $project = Project::findOrFail($projectId);

            $this->authorize('update', $project);

            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $userId = $request->input('user_id');
            if ($project->users()->where('user_id', $userId)->exists()) {
                return response()->json(['message' => 'User is already a member of this project.'], 400);
            }

            $project->users()->attach($userId, ['user_role' => 'ProjectMember']);

            return response()->json(['message' => 'User added successfully.']);
        } catch (\Exception $e) {
            Log::error('Failed to add user to project: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add user to this project.'], 500);
        }
    }

    public function removeMember(Request $request, $projectId, $userId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $userToRemove = User::findOrFail($userId);
            $requestUser = Auth::user();

            if ($project->project_status === 'Archived') {
                return redirect()->back()->with('error', 'Project is archived and no changes can be made.');
            }

            $isCoordinator = $project->coordinators->contains(function ($coordinator) use ($requestUser) {
                return $coordinator->pivot->user_id === $requestUser->id;
            });

            $canRemoveUser = $requestUser->isAdmin() || $isCoordinator || $project->creator_id === $requestUser->id;

            if (!$canRemoveUser) {
                return redirect()->back()->with('error', 'You do not have permission to remove users from this project.');
            }

            $isUserToRemoveCoordinator = $project->coordinators->contains(function ($coordinator) use ($userToRemove) {
                return $coordinator->pivot->user_id === $userToRemove->id;
            });

            $isRemovable = !$userToRemove->isAdmin() &&
                $userToRemove->id !== $project->creator_id &&
                !$isUserToRemoveCoordinator;

            if (!$isRemovable) {
                return redirect()->back()->with('error', 'You cannot remove this user.');
            }

            if (!$project->users()->where('user_id', $userToRemove->id)->exists()) {
                return redirect()->back()->with('error', 'User is not a member of this project.');
            }

            $project->users()->detach($userToRemove->id);

            $removalMessage = "{$userToRemove->username} has been removed from the project '{$project->project_name}'.";

            foreach ($project->users as $user) {
                if ($user->id !== $requestUser->id) {
                    ChangeRoleNotification::create([
                        'project_id' => $project->id,
                        'change_role_message' => $removalMessage,
                        'sender_id' => $requestUser->id,
                        'user_role_changed_id' => $userToRemove->id,
                        'receiver_id' => $user->id,
                        'sent_at' => now(),
                    ]);
                }
            }

            return redirect()->back()->with('success', 'User removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in removing user from project: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while trying to remove the user.');
        }
    }



    public function getContributors($projectId, Request $request)
    {
        $query = $request->input('search', '');
        $project = Project::with('coordinators')->findOrFail($projectId);
        $user = $request->user();
        $users = User::whereHas('projects', function ($query) use ($projectId) {
            $query->where('id', $projectId);
        })
            ->where('id', '!=', 1)
            ->where('username', 'like', "%{$query}%")
            ->paginate(5);

        $isMember = $project->creator_id != $user->id && !$project->coordinators->contains($user->id);

        $html = '';

        foreach ($users as $member) {
            $html .= view('partials.user_item', compact('member', 'isMember', 'project'))->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $users->hasMorePages(),
        ]);
    }

    public function archiveProject($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $this->authorize('update', $project);
            $project->project_status = 'Archived';
            $project->save();

            return redirect()->back()->with('success', 'Project archived successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to archive project: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function unarchiveProject($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $this->authorize('unarchive', $project);
            $project->project_status = 'Active';
            $project->save();

            return redirect()->back()->with('success', 'Project unarchived successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to unarchive project: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function viewYourProjects(Request $request)
    {
        try {
            $filter = $request->query('filter', 'all');
            $page = $request->query('page', 1);
            $perPage = 9;

            $query = match ($filter) {
                'all' => Project::where('creator_id', Auth::id())
                    ->orWhereHas('roles', function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                'myProjects' => Project::where('creator_id', Auth::id()),
                'sharedProjects' => Project::whereHas('roles', function ($query) {
                    $query->where('user_id', Auth::id());
                })->where('creator_id', '!=', Auth::id()),
                'archivedProjects' => Project::where('project_status', 'Archived')
                    ->where(function ($query) {
                        $query->where('creator_id', Auth::id())
                            ->orWhereHas('roles', function ($subQuery) {
                                $subQuery->where('user_id', Auth::id());
                            });
                    }),
                default => Project::where('id', 0),
            };

            $total = $query->count();
            $projects = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('partials.project_cards', compact('projects'))->render(),
                    'hasMore' => ($page * $perPage) < $total
                ]);
            }

            $hasMore = $total > $perPage;
            return view('pages.view_your_projects', compact('projects', 'filter', 'hasMore'));
        } catch (\Exception $exception) {
            Log::error('Failed to fetch projects: ' . $exception->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to fetch projects'], 500);
            }
            return redirect()->back()->with('error', 'Failed to fetch projects. Please try again.');
        }
    }

    public function promote($projectId, $memberId)
    {
        $project = Project::findOrFail($projectId);
        $member = User::findOrFail($memberId);

        if ($project->project_status == 'Archived') {
            return redirect()->back()->with('error', 'Project is archived.');
        }

        if (!$project->users->contains($member)) {
            return redirect()->route('project.show', $projectId)->with('error', 'User is not part of the project.');
        }

        if ($project->coordinators->contains($member)) {
            return redirect()->route('project.show', $projectId)->with('error', 'User is already a coordinator.');
        }

        $project->users()->updateExistingPivot($member->id, ['user_role' => 'ProjectCoordinator']);

        $promotionMessage = "{$member->username} has been promoted to Coordinator of the Project '{$project->project_name}'.";

        foreach ($project->users as $user) {
            if ($user->id !== Auth::id()) {
                ChangeRoleNotification::create([
                    'project_id' => $project->id,
                    'change_role_message' => $promotionMessage,
                    'sender_id' => Auth::id(),
                    'user_role_changed_id' => $member->id,
                    'receiver_id' => $user->id,
                    'sent_at' => now(),
                ]);
            }
        }

        return redirect()->route('project.show', $projectId)->with('success', 'User promoted to Coordinator.');
    }

    public function demote($projectId, $memberId)
    {
        $project = Project::findOrFail($projectId);
        $member = User::findOrFail($memberId);

        if ($project->project_status == 'Archived') {
            return redirect()->back()->with('error', 'Project is archived.');
        }

        if (!$project->coordinators->contains($member)) {
            return redirect()->route('project.show', $projectId)->with('error', 'User is not a coordinator.');
        }

        $project->coordinators()->updateExistingPivot($member->id, ['user_role' => 'ProjectMember']);

        $demotionMessage = "{$member->username} has been demoted to Member of the Project '{$project->project_name}'.";

        foreach ($project->users as $user) {
            if ($user->id !== Auth::id()) {
                ChangeRoleNotification::create([
                    'project_id' => $project->id,
                    'change_role_message' => $demotionMessage,
                    'sender_id' => Auth::id(),
                    'user_role_changed_id' => $member->id,
                    'receiver_id' => $user->id,
                    'sent_at' => now(),
                ]);
            }
        }

        return redirect()->route('project.show', $projectId)->with('success', 'User demoted to Member.');
    }

    public function filter(Request $request)
    {
        $filter = $request->query('filter', 'all');

        try {
            $projects = match ($filter) {
                'all' => Project::where(function ($query) {
                    $query->where('creator_id', Auth::id())
                        ->orWhereHas('roles', function ($subQuery) {
                            $subQuery->where('user_id', Auth::id());
                        });
                })->get(),
                'sharedProjects' => Project::whereHas('roles', function ($query) {
                    $query->where('user_id', Auth::id());
                })->where('creator_id', '!=', Auth::id())->get(),
                'favoriteProjects' => Auth::user()->favoriteProjects()->get(),
                'myProjects' => Project::where('creator_id', Auth::id())->get(),
                'archivedProjects' => Project::where('project_status', 'Archived')
                    ->where(function ($query) {
                        $query->where('creator_id', Auth::id())
                            ->orWhereHas('roles', function ($subQuery) {
                                $subQuery->where('user_id', Auth::id());
                            });
                    })->get(),
                default => collect(),
            };

            $sharedProjects = Project::whereHas('roles', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->where('creator_id', '!=', Auth::id())
                ->with('tasks')
                ->orderBy('created_at', 'desc')
                ->get();

            $tasksDue = Task::whereIn('project_id', $projects->pluck('id')->merge($sharedProjects->pluck('id')))
                ->whereBetween('deadline', [now(), now()->addDays(7)])
                ->orderBy('deadline', 'asc')
                ->paginate(5);

            return view('pages.projects', compact('projects', 'sharedProjects', 'tasksDue', 'filter'));
        } catch (\Exception $exception) {
            Log::error('Failed to filter projects: ' . $exception->getMessage());
            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function filterNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            $projects = Project::where(function ($query) {
                $query->where('creator_id', Auth::id())
                    ->orWhereHas('roles', function ($subQuery) {
                        $subQuery->where('user_id', Auth::id());
                    });
            })->get();

            $sharedProjects = Project::whereHas('roles', function ($query) {
                $query->where('user_id', Auth::id());
            })
                ->where('creator_id', '!=', Auth::id())
                ->with('tasks')
                ->orderBy('created_at', 'desc')
                ->get();

            $tasksDue = Task::whereIn('project_id', $projects->pluck('id')->merge($sharedProjects->pluck('id')))
                ->whereBetween('deadline', [now(), now()->addDays(7)])
                ->orderBy('deadline', 'asc')
                ->paginate(5);

            $taskCompletedNotifications = TaskCompletedNotification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $taskAssignedNotifications = TaskAssignedNotification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $notifications = $taskCompletedNotifications
                ->merge($taskAssignedNotifications)
                ->sortByDesc('created_at')
                ->take(5);

            return view('pages.projects', compact('notifications', 'tasksDue'));
        } catch (\Exception $exception) {
            Log::error('Failed to filter notifications: ' . $exception->getMessage());
            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function leave(Project $project)
    {
        $user = Auth::user();

        if (!$project->members()->where('users.id', $user->id)->exists()) {
            return redirect()->back()
                ->with('error', 'You are not a member of this project.');
        }

        DB::table('project_role')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->delete();
        
        $leaveMessage = "{$user->username} has left the Project '{$project->project_name}'.";
            
            foreach ($project->users as $allusers) {
                if ($allusers->id !== Auth::id()) {
                    ChangeRoleNotification::create([
                        'project_id' => $project->id,
                        'change_role_message' => $leaveMessage,
                        'sender_id' => Auth::id(),
                        'user_role_changed_id' => Auth::id(),
                        'receiver_id' => $allusers->id,
                        'sent_at' => now(),
                    ]);
                }
            }

        $project->users()->detach($user->id);

        return redirect()->route('projects')
            ->with('success', 'You have left the project successfully.');

    }
    public function edit(Project $project)
    {
        return view('pages.edit_project', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        if($project->project_status == 'Archived') {
            return redirect()->back()->with('error', 'Project is archived.');
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'required|string|max:255',
        ]);

        $project->project_name = $request->input('project_name');
        $project->project_description = $request->input('project_description');

        if ($project->save()) {
            return redirect()->route('project.show', $project->id)
                ->with('success', 'Project updated successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to update project. Please try again.');
    }

    public function favorite(Request $request, $projectId)
    {
        try {
            $user = Auth::user();
            $project = Project::findOrFail($projectId);

            if (DB::table('favorite_project')->where('user_id', $user->id)->where('project_id', $project->id)->exists()) {
                return response()->json(['favorited' => true, 'message' => 'Project is already a favorite'], 200);
            }

            DB::table('favorite_project')->insert([
                'user_id' => $user->id,
                'project_id' => $project->id
            ]);

            return response()->json(['favorited' => true, 'message' => 'Project added to favorites successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['favorited' => false, 'message' => 'Failed to add project to favorites.'], 500);
        }
    }

    public function unfavorite(Request $request, $projectId)
    {
        try {
            $user = Auth::user();
            $project = Project::findOrFail($projectId);

            if (!DB::table('favorite_project')->where('user_id', $user->id)->where('project_id', $project->id)->exists()) {
                return response()->json(['favorited' => false, 'message' => 'Project is not in favorites'], 200);
            }

            DB::table('favorite_project')->where('user_id', $user->id)->where('project_id', $project->id)->delete();

            return response()->json(['favorited' => false, 'message' => 'Project removed from favorites successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['favorited' => true, 'message' => 'Failed to remove project from favorites.'], 500);
        }
    }

}
