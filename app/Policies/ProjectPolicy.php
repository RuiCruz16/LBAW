<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use const http\Client\Curl\AUTH_ANY;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->creator_id === $user->id || $project->users->contains('id', $user->id) || Auth::user()->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $project->users->contains('id', $user->id) && $project->project_status != "Archived";
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->coordinators->contains($user) || $project->creator_id === $user->id;
    }

    public function unarchive(User $user, Project $project): bool
    {
        return $project->users->contains('id', $user->id) && $project->project_status == "Archived";
    }
}

