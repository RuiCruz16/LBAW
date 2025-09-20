<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'comment_content',
        'created_at',
        'task_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Get the task associated with this comment.
     */
    public function task() : BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Get the user who created this comment.
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the parent comment (if any).
     */
    public function parent() : BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'comment_id');
    }

    /**
     * Get the child comments (replies).
     */
    public function children() : HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id', 'comment_id');
    }
}