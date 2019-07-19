<?php

namespace App\Models;


class WatchlistRule extends Model
{

    const GreaterThanNoRule = 1;
    const LesserThanNoRule = 2;
    const GreaterThanProgressively = 3;
    const LesserThanRegressively = 4;

    public function watchlist()
    {
        return $this->belongsTo(Watchlist::class, 'watchlist_id');
    }
}
