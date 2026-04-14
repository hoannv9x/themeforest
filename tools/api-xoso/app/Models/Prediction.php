<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'numbers', 'meta', 'algorithm', 'accuracy', 'region', 'created_at', 'updated_at'];

    protected $casts = [
        'numbers' => 'array',
        'meta' => 'array'
    ];
}
