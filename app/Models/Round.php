<?php

namespace App\Models;

use Carbon\Carbon;

class Round extends Model
{
    protected $fillable = [
        'exchange_account_id',
        'start_at',
        'end_at',
        'is_canceled',
        'cycle_count',
        'cycle_length',
        'minimum_fr_count',
        'price_volume_count',
        'ati_count',
        'ati_pd_count',
        'limiters_count',
        'purchases',
        'is_processed',
        'strategy'
    ];

    protected $dates = [
        'last_processed_at',
        'created_at',
        'modified_at',
        'start_at',
        'end_at'
    ];

    public function exchangeAccount()
    {
        return $this->belongsTo(ExchangeAccount::class);
    }

    public function cycles()
    {
        return $this->hasMany(Cycle::class);
    }

    public function scopeByExchangeAccount($query, $exchangeAccount)
    {
        if ($exchangeAccount instanceof ExchangeAccount) {
            $exchangeAccount = $exchangeAccount->id;
        }

        return $query->where('exchange_account_id', $exchangeAccount);
    }

    public function scopeActive($query)
    {
        return $query->where('start_at', '<=', Carbon::now())
            ->where('is_processed', false)
            ->where('is_canceled', false);
    }

    public function setHoldersAttribute($value)
    {
        $this->attributes['holders_json'] = json_encode($value);
    }

    public function getHoldersAttribute()
    {
        return json_decode(array_get($this->attributes, 'holders_json'), true);
    }

    public function setPurchasesAttribute($value)
    {
        $this->attributes['purchases_json'] = json_encode($value);
    }

    public function getPurchasesAttribute()
    {
        return json_decode(array_get($this->attributes, 'purchases_json'), true);
    }

    public function getCurrentCycleAttribute()
    {
        return $this->cycles()
            ->where('start_at', '<=', Carbon::now())
            ->where('end_at', '>', Carbon::now())
            ->first();
    }

    public function getLastUnprocessedCycleAttribute()
    {
        return $this->cycles()
            ->where('is_processed', false)
            ->orderBy('end_at', 'asc')
            ->first();
    }

    public function getLastCycleAttribute()
    {
        return $this->cycles()
            ->orderBy('end_at', 'desc')
            ->first();
    }

    public function getIsOverAttribute()
    {
        return Carbon::now()->greaterThanOrEqualTo($this->end_at);
    }
}