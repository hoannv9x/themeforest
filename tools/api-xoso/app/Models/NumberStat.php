<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberStat extends Model
{
    use HasFactory;
    protected $fillable = [
        'number',
        'region',
        'total_count',
        'current_gap',
        'total_count_7_days',
        'total_count_30_days',
        'total_count_90_days',
        'total_count_180_days',
        'total_count_365_days',
        'last_appeared_at',
        'updated_at',
        'never_hit'
    ];
    public $timestamps = false;
}
