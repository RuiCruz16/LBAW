<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignedTask extends Model{
    use HasFactory;
    
    protected $table = 'assigned_task';

    protected $fillable = [
        'user_id',
        'task_id',
    ] ;

    public $timestamps = false;

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function task(){
        return $this->belongsTo(Task::class);
    }


}