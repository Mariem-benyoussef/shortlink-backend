<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'shortlink_id',
        'ip',
        'user_agent',
        'referrer',
        'country',
        'device',
    ];

    public function shortlink()
    {
        return $this->belongsTo(Shortlink::class);
    }


}
