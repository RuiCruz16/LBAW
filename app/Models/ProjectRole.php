<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRole extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'project_role';
    public $incrementing = false;

    protected $primaryKey = ['user_id', 'project_id'];

    protected $fillable = [
        'user_id',
        'project_id',
        'user_role'
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}