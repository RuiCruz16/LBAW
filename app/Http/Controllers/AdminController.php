<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function indexUsers()
    {
        $users = User::where('id', '!=', 1)->paginate(6);
        return view('pages.admin_users', compact('users'));
    }

    public function indexProjects()
    {
        $projects = Project::paginate(6);
        return view('pages.admin_projects', compact('projects'));
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('user');

        $users = User::where('id', '!=', 1)
        ->where(function($q) use ($query) {
            $q->where('username', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->paginate(6);

        $projects = Project::paginate(6);
        $currentPage = 'users';

        return view('pages.admin_users', compact('users', 'projects', 'currentPage'));
    }

    public function searchProjects(Request $request)
    {
        $query = $request->get('project', '');

        $users = User::paginate(6);

        if (!empty($query)) {
            $projectsQuery = DB::table('project')
                ->select('id', 'project_name', 'project_description')
                ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query]);

            $projects = $projectsQuery->paginate(6);

            $projects->getCollection()->transform(function ($project) {
                return (object) [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'project_description' => $project->project_description,
                    'url' => route('project.show', ['id' => $project->id]),
                ];
            });
        } else {
            $projects = Project::select('id', 'project_name', 'project_description')
                ->paginate(6);

            $projects->getCollection()->transform(function ($project) {
                return (object) [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'project_description' => $project->project_description,
                    'url' => route('project.show', ['id' => $project->id]),
                ];
            });
        }

        $currentPage = 'projects';

        return view('pages.admin_projects', compact('users', 'projects', 'currentPage'));
    }
}