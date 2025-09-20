<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;

        return $project && ($project->creator_id === $user->id || $project->users()->where('users.id', $user->id)->exists() || $user->isAdmin());
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->users->contains('id', $user->id) && $task->project->project_status != "Archived";
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->users->contains('id', $user->id) && $task->project->project_status != "Archived";
    }
}
