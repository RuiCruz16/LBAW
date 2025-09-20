<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvitation extends Model
{

    use HasFactory;

    protected $table = 'project_invitation';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'invitation_message',
        'sender_id',
        'receiver_id',
        'sent_at',
        'response'
    ];


    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}