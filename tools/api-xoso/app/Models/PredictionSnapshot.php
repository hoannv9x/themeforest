<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'region',
        'is_vip',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'is_vip' => 'boolean',
        'date' => 'date',
    ];
}
