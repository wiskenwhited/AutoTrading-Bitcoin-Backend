<?php

namespace App\Models;


use Carbon\Carbon;

class UserPackage extends Model
{
    protected $appends = ['test_expired', 'test_time_left', 'all_expired', 'all_time_left'];

    protected $casts = [
        'all_live_started' => 'datetime',
        'all_live_valid_until' => 'datetime',
        'test_started' => 'datetime',
        'test_valid_until' => 'datetime',
    ];

    protected $dates = [
        'all_live_started',
        'all_live_valid_until',
        'test_started',
        'test_valid_until',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exchanges()
    {
        return $this->hasMany(UserPackageExchange::class, 'user_package_id');
    }

    public function getTestExpiredAttribute()
    {
        return (is_null($this->test_valid_until) || $this->test_valid_until < Carbon::now());
    }

    public function getTestTimeLeftAttribute()
    {
        if (!is_null($this->test_valid_until)) {
            if ($this->test_valid_until > Carbon::now())
                return $this->test_valid_until->diffInSeconds();
        };
        return 0;
    }

    public function getAllExpiredAttribute()
    {
        return (is_null($this->all_live_valid_until) || $this->all_live_valid_until < Carbon::now());
    }

    public function getAllTimeLeftAttribute()
    {
        if (!is_null($this->all_live_valid_until)) {
            if ($this->all_live_valid_until > Carbon::now())
                return $this->all_live_valid_until->diffInSeconds();
        };
        return 0;
    }
}