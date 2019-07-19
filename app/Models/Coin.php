<?php

namespace App\Models;

use Carbon\Carbon;

class Coin extends Model
{
    use FormatsCurrencyTrait;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'name',
        'symbol',
        'rank',
        'price_usd',
        'price_btc',
        'volume_usd_24h',
        'market_cap_usd',
        'available_supply',
        'total_supply',
        'percent_change_1h',
        'percent_change_24h',
        'percent_change_7d',
        'last_updated'
    ];
    protected $appends = [
        'local_currency_code',
        'price_local_currency',
        'formatted_price_local_currency',
        'formatted_price_usd',
    ];

    public function scopeFindBySymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol)->first();
    }

    public function setLastUpdatedAttribute($value)
    {
        if (is_numeric($value)) {
            $value = Carbon::createFromTimestampUTC($value);
        }
        $this->attributes['last_updated'] = $value;
    }

    public function scopeLimitAndOrderBy($query, $limit, $offset, $orderBy)
    {
        if (is_array($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy($orderBy ?: 'rank');
        }

        if (! is_null($limit) && ! is_null($offset)) {
            $query->limit($limit)
                ->offset($offset);
        }

        return $query;
    }

    public function getLocalCurrencyCodeAttribute()
    {
        return array_get($this->virtual, 'local_currency_code');
    }

    public function getPriceLocalCurrencyAttribute()
    {
        return array_get($this->virtual, 'price_local_currency');
    }

    public function getFormattedPriceUsdAttribute()
    {
        return $this->formatCurrency($this->price_usd, 'USD');
    }

    public function getFormattedPriceLocalCurrencyAttribute()
    {
        return $this->formatCurrency(
            $this->price_local_currency,
            array_get($this->virtual, 'local_currency_code')
        );
    }
}