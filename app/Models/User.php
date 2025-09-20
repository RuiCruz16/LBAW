<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'biography',
        'user_status',
        'last_login',
        'created_at',
        'is_admin',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'is_admin',
        'remember_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function projectRoles() : HasMany
    {
        return $this->hasMany(ProjectRole::class, 'user_id');
    }

    public function projects() : BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_role', 'user_id', 'project_id')
            ->withPivot('user_role');
    }

    public function isAdmin() : bool
    {
        return $this->attributes['is_admin'] == true;
    }

    public function isProjectCoordinator($project) : bool
    {
        return $project->coordinators->contains($this->id);
    }
    public function image()
    {
        return $this->hasOne(Image::class, 'user_id');
    }
    public function tasks() : BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'assigned_task', 'user_id', 'task_id')->withPivot('id');
    }

    public function assignedtask()
    {
        return $this->hasMany(AssignedTask::class, 'user_id');
    }

    public function favoriteProjects()
    {
        return $this->belongsToMany(Project::class, 'favorite_project', 'user_id', 'project_id');
    }
}
