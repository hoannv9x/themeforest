<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberStat extends Model
{
    use HasFactory;
    protected $table = 'number_stats';
    protected $fillable = ['number', 'current_gap', 'total_gap'];
    public $timestamps = false;
}
