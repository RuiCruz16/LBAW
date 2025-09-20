<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRoleNotification extends Model
{
    use HasFactory;

    protected $table = 'change_role_notifications';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'change_role_message',
        'sender_id',
        'sent_at',
        'user_role_changed_id',
        'receiver_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}