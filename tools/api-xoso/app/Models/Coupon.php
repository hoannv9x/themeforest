<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_percent',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'user_id',
        'source',
        'meta',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateUniqueCode(int $length = 10): string
    {
        $length = max(6, min(32, $length));

        for ($i = 0; $i < 10; $i++) {
            $code = Str::upper(Str::random($length));
            if (!self::where('code', $code)->exists()) {
                return $code;
            }
        }

        return Str::upper(Str::uuid()->toString());
    }
}
