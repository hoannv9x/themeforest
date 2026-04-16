<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Algorithm extends Model
{
    const CODES = [
        'gan',      // lô gan
        'roi',      // lô rơi
        'trend',    // 7 ngày gần
        'cycle',    // chu kỳ
        'mirror',   // lộn
        'ai'        // tổng hợp

    ];
}
