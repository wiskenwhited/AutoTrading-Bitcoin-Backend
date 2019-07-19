<?php

namespace App\Models;


use Carbon\Carbon;

class UserPackageExchange extends Model
{
    protected $appends = ['live_expired', 'live_time_left'];

    protected $casts = [
        'live_started' => 'datetime',
        'live_valid_until' => 'datetime',
    ];

    protected $dates = [
        'live_started',
        'live_valid_until',
    ];


    public function getLiveExpiredAttribute()
    {
        return (is_null($this->live_valid_until) || $this->live_valid_until < Carbon::now());
    }

    public function getLiveTimeLeftAttribute()
    {
        if (!is_null($this->live_valid_until)) {
            if ($this->live_valid_until > Carbon::now())
                return $this->live_valid_until->diffInSeconds();
        };
        return 0;
    }
}