<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
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

    public function index($taskId)
    {
        $task = Task::findOrFail($taskId);

        $comments = $task->comments()
            ->with('user:id,username')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments, 201);
    }

    public function store(Request $request, $taskId)
    {
        try {
            $validated = $request->validate([
                'comment_content' => 'required|string|max:1000',
            ]);

            $task = Task::with('assignedUsers')->findOrFail($taskId);

            $comment = new Comment();
            $comment->comment_content = $validated['comment_content'];
            $comment->task_id = $taskId;
            $comment->user_id = Auth::id();
            $comment->created_at = now();

            $comment->save();

            $comment->load('user:id,username');

            return response()->json($comment, 201);
        } catch (Exception $e) {
            Log::error('Failed to store comment: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to add comment'], 500);
        }
    }


    public function destroy($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        $this->authorize('delete', $comment);

        if(!$this->authorize('delete', $comment)) {
            return response()->json(['error' => 'You dont have enough permissions to delete this comment.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }

}
