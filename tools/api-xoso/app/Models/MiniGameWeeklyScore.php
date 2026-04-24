<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniGameWeeklyScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start',
        'week_end',
        'user_id',
        'correct_count',
        'is_winner',
        'meta',
    ];

    protected $casts = [
        'week_start' => 'date:Y-m-d',
        'week_end' => 'date:Y-m-d',
        'is_winner' => 'boolean',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
