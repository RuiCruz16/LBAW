<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'task';

    protected $fillable = [
        'task_title',
        'task_description',
        'created_at',
        'deadline',
        'task_status_id',
        'task_priority',
        'project_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function users() : BelongsToMany
{
    return $this->belongsToMany(User::class, 'assigned_task', 'task_id', 'user_id')->withPivot('id');
}
public function assignedtask()
{
    return $this->hasMany(AssignedTask::class, 'user_id');
}
    public function status() : BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function comments() : HasMany
    {
        return $this->hasMany(Comment::class, 'task_id');
    }

    public function notifications() : HasMany
    {
        return $this->hasMany(TaskNotification::class, 'task_id');
    }

    public function assignedUsers() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assigned_task', 'task_id', 'user_id');
    }
    
}
