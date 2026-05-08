<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempResult extends Model
{
    protected $table = 'temp_results';

    protected $fillable = [
        'date',
        'region',
        'province_code',
        'raw_data',
        'is_complete',
        'attempts',
        'last_fetched_at',
        'completed_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'date' => 'date:Y-m-d',
        'is_complete' => 'boolean',
        'last_fetched_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

