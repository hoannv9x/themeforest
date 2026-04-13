<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;

    protected $fillable = ['result_id', 'number', 'prize'];
    public $timestamps = false;
    public function result()
    {
        return $this->belongsTo(Result::class);
    }
}
