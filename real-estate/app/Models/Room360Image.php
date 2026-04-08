<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room360Image extends Model
{
    protected $table = 'room_360_images';
    protected $fillable = [
        'room_id',
        'panorama_url',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
