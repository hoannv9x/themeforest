<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'event',
        'is_active',
        'last_triggered_at',
        'last_status_code',
        'last_response',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
}
