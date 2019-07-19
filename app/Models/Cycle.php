<?php

namespace App\Models;


use Carbon\Carbon;

class Cycle extends Model
{
    protected $fillable = [
        'round_id',
        'index',
        'start_at',
        'end_at',
        'is_processed'
    ];

    protected $dates = [
        'created_at',
        'modified_at',
        'start_at',
        'end_at'
    ];

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function getIsOverAttribute()
    {
        return Carbon::now()->greaterThanOrEqual($this->end_at);
    }

    public function purchases()
    {
        return $this->hasMany(CyclePurchase::class)
            ->with(['buyTrades', 'boughtTrades', 'sellTrades', 'soldTrades']);
    }
}