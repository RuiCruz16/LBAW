<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Task;
use App\Models\User;

class TaskAssignedNotification extends Model
{

    use HasFactory;

    protected $table = 'task_notifications';

    public $timestamps = false;

    protected $fillable = [
        'created_at',
        'task_id',
        'user_id',
        'notify_type'
    ];


    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}