<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskNotification extends Model
{
    use HasFactory;

    protected $table = 'task_notifications';

    protected $fillable = [
        'created_at',
        'task_id',
        'notify_type',
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get the task associated with this notification.
     */
    public function task() : BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
