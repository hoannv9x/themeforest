<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'plan_key',
        'plan_name',
        'duration_days',
        'amount',
        'transfer_content',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'status',
        'manual_review_status',
        'manual_review_requested_at',
        'paid_at',
        'cancelled_at',
        'cancelled_reason',
        'rejected_at',
        'rejected_reason',
        'rejected_by_user_id',
        'meta',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'manual_review_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }
}
