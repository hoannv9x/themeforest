<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniGameDailyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_date',
        'total_participants',
        'top_numbers',
        'predicted_numbers',
        'leader_number',
        'leader_votes',
        'ai_suggestion',
        'cutoff_at',
        'finalized_at',
    ];

    protected $casts = [
        'prediction_date' => 'date:Y-m-d',
        'top_numbers' => 'array',
        'predicted_numbers' => 'array',
        'ai_suggestion' => 'array',
        'cutoff_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];
}
