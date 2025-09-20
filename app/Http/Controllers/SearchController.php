<?php
namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller {
    public function search(Request $request)
    {
        $query = $request->input('query');
        $user = $request->user();

        if (!$query) {
            return redirect()->route('projects');
        }

        try {
            $tasks = Task::with('project')
            ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
                ->whereIn('project_id', function ($query) use ($user) {
                    $query->select('project_id')
                        ->from('project_role')
                        ->where('user_id', $user->id);
                })
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'task_title' => $task->task_title,
                        'task_description' => $task->task_description,
                        'project_url' => route('project.show', ['id' => $task->project_id]) . "?task={$task->id}",
                        'project_name' => $task->project->project_name,
                    ];
                });

            $projects = DB::table('project')
                ->select('id', 'project_name', 'project_description')
                ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
                ->get()
                ->filter(function ($project) use ($user) {
                    return $user->can('view', Project::find($project->id));
                })
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'project_name' => $project->project_name,
                        'project_description' => $project->project_description,
                        'url' => route('project.show', ['id' => $project->id]),
                    ];
                });

            $users = DB::table('users')
                ->select('id', 'username', 'email')
                ->where('username', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->get();

            return view('pages.search', [
                'query' => $query,
                'users' => $users,
                'tasks' => $tasks->values(),
                'projects' => $projects->values(),
            ]);
        } catch (Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json(['error' => 'Search Error'], 500);
        }
    }
}