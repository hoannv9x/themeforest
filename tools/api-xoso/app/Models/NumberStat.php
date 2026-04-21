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
        'last_appeared_at_db',
        'max_gap',
        'current_gap_db',
        'max_gap_db',
        'std_gap',
        'avg_gap',
        'std_gap_db',
        'avg_gap_db',
        'updated_at'
    ];
    public $timestamps = false;
}
