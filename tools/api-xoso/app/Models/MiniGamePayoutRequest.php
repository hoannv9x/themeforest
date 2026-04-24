<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniGamePayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'status',
        'note',
    ];

    protected $casts = [
        'week_start' => 'date:Y-m-d',
        'week_end' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
