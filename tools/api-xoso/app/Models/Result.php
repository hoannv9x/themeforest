<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'region', 'province_code', 'raw_data', 'created_at'];

    public $timestamps = false;

    protected $casts = [
        'raw_data' => 'array',
        'date' => 'date:Y-m-d'
    ];

    public function numbers()
    {
        return $this->hasMany(Number::class);
    }
}
