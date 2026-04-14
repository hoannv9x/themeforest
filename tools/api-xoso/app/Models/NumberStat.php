<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberStat extends Model
{
    use HasFactory;
    protected $fillable = ['number', 'current_gap', 'total_count', 'last_appeared_at', 'updated_at', 'never_hit'];
    public $timestamps = false;
}
