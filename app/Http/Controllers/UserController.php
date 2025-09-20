<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
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

    public function index(Request $request)
    {
        $query = User::query();
        $projectId = (int)$request->input('project_id');
        if ($request->has('user') && $request->user) {
            $query->where(function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->user . '%')
                    ->orWhere('email', 'like', '%' . $request->user . '%');
            });
        }

        if ($projectId) {
            $project = Project::with('users:id')->find($projectId);

            if ($project && $project->users->isNotEmpty()) {
                $projectMemberIds = $project->users->pluck('id')->toArray();
                $query->whereNotIn('id', $projectMemberIds);
            }
        }
        $users = $query->where('id', '!=', 1)->paginate(6);

        $projects = Project::paginate(6);

        if ($request->wantsJson()) {
            try {
                $html = view('partials.user_list', compact('users', 'projectId'))->render();
                return response()->json(['html' => $html]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to load user list.'], 500);
            }
        }


        return view('pages.admin_users', compact('users', 'projects'));
    }

    public function blockUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            $user->user_status = 'Blocked';
            $user->save();

            return redirect()->route('admin.users')->with('success', "User {$user->username} has been blocked.");

        } catch (\Exception $e) {
            Log::error('Error blocking user: ' . $e->getMessage());
            return redirect()->route('admin.users')->with('error', 'Failed to block user. Please try again.');
        }
    }

    public function unblockUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            $user->user_status = 'Active';
            $user->save();

            return redirect()->route('admin.users')->with('success', "User {$user->username} has been unblocked.");

        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Failed to unblock user.');
        }
    }

    public function searchUsers(Request $request)
    {
        try {
            $query = User::query();
            $projectId = $request->input('project_id');
            $searchTerm = $request->input('user');

            $query->where('id', '!=', 1);

            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('username', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($projectId) {
                $existingMembers = ProjectRole::where('project_id', $projectId)
                    ->pluck('user_id');
                $query->whereNotIn('id', $existingMembers);
            }

            if (Auth::check()) {
                $query->where('id', '!=', Auth::id());
            }

            $users = $query->get(['id', 'username', 'email']);

            return response()->json([
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error finding users: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error finding users',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}