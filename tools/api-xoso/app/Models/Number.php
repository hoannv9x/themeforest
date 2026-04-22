<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;

    protected $fillable = ['result_id', 'number', 'prize', 'raw_number'];

    public $timestamps = false;


    const REGION_MB = 'mb';
    const REGION_MN = 'mn';
    const REGION_MT = 'mt';
    const REGIONS = [self::REGION_MB, self::REGION_MN, self::REGION_MT];

    const MB_TRADITION = 'MB_TRADITION';

    public function result()
    {
        return $this->belongsTo(Result::class);
    }
}
