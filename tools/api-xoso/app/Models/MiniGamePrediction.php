<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniGamePrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prediction_date',
        'numbers',
        'source',
        'predictor_name',
        'hit_count',
        'evaluated_at',
    ];

    protected $casts = [
        'prediction_date' => 'date:Y-m-d',
        'numbers' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
