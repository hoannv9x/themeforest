<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'plan_key',
        'name',
        'duration_days',
        'amount',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'amount' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}

