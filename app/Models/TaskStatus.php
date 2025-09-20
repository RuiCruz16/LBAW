<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    use HasFactory;

    protected $table = 'task_status';

    protected $fillable = [
        'task_status_name',
    ];

    /**
     * Get the tasks associated with this status.
     */
    public function tasks() : HasMany
    {
        return $this->hasMany(Task::class, 'task_status_id');
    }
}