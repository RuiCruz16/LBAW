<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class AlreadyShowedNotification extends Model
{

    use HasFactory;

    protected $table = 'already_showed_notification';

    protected $fillable = [
        'user_id',
        'notification_id',
    ];
    public $timestamps = false;

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function hasAlreadyShowedNotification($user_id, $notification_id)
    {
        return $this->where('user_id', $user_id)->where('notification_id', $notification_id)->exists();
    }
}