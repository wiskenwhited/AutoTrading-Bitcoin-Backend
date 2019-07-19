<?php

namespace App\Models;


class CurrencyRate extends Model
{
    protected $fillable = [
        'base',
        'target',
        'rate'
    ];

    public function scopeBaseAndTarget($query, $base, $target)
    {
        return $query->where('base', $base)->where('target', $target);
    }
}