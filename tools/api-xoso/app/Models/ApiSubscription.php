<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_key',
        'plan_name',
        'started_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
