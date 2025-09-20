<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Project extends Model
{
    use HasFactory;

    protected $table = 'project';

    protected $fillable = [
        'project_name',
        'project_description',
        'project_status',
        'created_at',
        'creator_id'
    ];

    const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime'
    ];
    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function tasks() : HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function roles() : HasMany
    {
        return $this->hasMany(ProjectRole::class, 'project_id');
    }

    public function isFavoritedBy(User $user): bool
    {
        return DB::table('favorite_project')
            ->where('user_id', $user->id)
            ->where('project_id', $this->id)
            ->exists();
    }

    public function tasksGroupedByStatus()
    {
        return $this->tasks()
            ->get()
            ->groupBy('status');
    }

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_role', 'project_id', 'user_id')
            ->withPivot('user_role');
    }

    public function coordinators() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_role', 'project_id', 'user_id')->wherePivot('user_role', 'ProjectCoordinator');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_role', 'project_id', 'user_id')
                    ->wherePivot('user_role', 'ProjectMember');
    }

}