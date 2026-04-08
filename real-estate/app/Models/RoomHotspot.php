<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomHotspot extends Model
{
    protected $table = 'room_hotspots';
    protected $fillable = [
        'from_room_id',
        'to_room_id',
        'pitch',
        'yaw',
        'text'
    ];

    public function fromRoom()
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom()
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }
}
