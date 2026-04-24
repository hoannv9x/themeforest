<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permission',
        'vip_expired_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'vip_expired_at' => 'datetime',
        ];
    }

    const ROLE_VIP = 'vip';
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    const PERMISSION_USER = 'user';
    const PERMISSION_DEVELOPER = 'developer';

    public function miniGamePredictions()
    {
        return $this->hasMany(MiniGamePrediction::class);
    }

    public function miniGameWeeklyScores()
    {
        return $this->hasMany(MiniGameWeeklyScore::class);
    }

    public function miniGamePayoutRequests()
    {
        return $this->hasMany(MiniGamePayoutRequest::class);
    }
}
