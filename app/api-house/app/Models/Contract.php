<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'room_tenant_id',
    'file_path',
    'tenant_signature_path',
    'landlord_signature_path',
    'signed_at',
])]
class Contract extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function roomTenant(): BelongsTo
    {
        return $this->belongsTo(RoomTenant::class);
    }
}

