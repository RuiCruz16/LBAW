<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentPolicy
{
    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment)
    {

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $comment->user_id) {
            return true;
        }

        $project = $comment->task->project;

        if ($user->id === $project->creator_id) {
            return true;
        }

        return $project->coordinators()
            ->where('user_id', $user->id)
            ->exists();
    }
}