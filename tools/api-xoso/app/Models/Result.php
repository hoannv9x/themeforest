<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'region', 'province_code', 'raw_data'];

    public $timestamps = false;

    protected $casts = [
        'raw_data' => 'array',
        'date' => 'date'
    ];

    public function numbers()
    {
        return $this->hasMany(Number::class);
    }
}
