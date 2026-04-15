<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlLog extends Model
{
    protected $table = 'crawl_logs';

    protected $fillable = [
        'source',
        'status',
        'message',
        'created_at'
    ];

    public $timestamps = false;
}
