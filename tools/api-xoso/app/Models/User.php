<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

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
        'api_expired_at',
        'vip_trial_used',
        'vip_trial_started_at'
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
            'vip_trial_used' => 'boolean',
            'vip_trial_started_at' => 'datetime',
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

    public function isVip(): bool
    {
        return $this->role === self::ROLE_VIP
            && $this->vip_expired_at
            && $this->vip_expired_at->isFuture();
    }

    public function isTrialActive(): bool
    {
        return $this->vip_trial_used
            && $this->vip_trial_started_at
            && $this->vip_trial_started_at->addDays(3)->isFuture();
    }

    public function hasUsedTrial(): bool
    {
        return (bool) $this->vip_trial_used;
    }

    public function getTrialRemainingDays(): ?int
    {
        if (!$this->isTrialActive()) {
            return null;
        }
        return max(0, $this->vip_trial_started_at->addDays(3)->diffInDays(Carbon::now()));
    }

    public function getVipRemainingDays(): ?int
    {
        if (!$this->isVip() || !$this->vip_expired_at) {
            return null;
        }
        return max(0, $this->vip_expired_at->diffInDays(Carbon::now()));
    }

    public function startVipTrial(): void
    {
        $this->update([
            'vip_trial_used' => true,
            'vip_trial_started_at' => Carbon::now(),
            'role' => self::ROLE_VIP,
            'vip_expired_at' => Carbon::now()->addDays(3),
        ]);
    }

    public function getVipStatus(): array
    {
        return [
            'is_vip' => $this->isVip(),
            'is_trial' => $this->isTrialActive(),
            'has_used_trial' => $this->hasUsedTrial(),
            'trial_remaining_days' => $this->getTrialRemainingDays(),
            'vip_remaining_days' => $this->getVipRemainingDays(),
            'vip_expired_at' => $this->vip_expired_at,
            'role' => $this->role,
        ];
    }
}
