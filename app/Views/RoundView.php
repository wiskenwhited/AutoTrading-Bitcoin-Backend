<?php

namespace App\Views;

use App\Models\Cycle;
use App\Models\FormatsCurrencyTrait;
use Carbon\Carbon;

class RoundView extends AbstractView
{
    use FormatsCurrencyTrait;

    protected $fields = [
        'active',
        'exchange_account_id',
        'start_at',
        'end_at',
        'progress',
        'cycle_count',
        'current_cycle',
        'minimum_fr_count',
        'minimum_fr_count',
        'price_volume_count',
        'ati_count',
        'ati_pd_count',
        'limiters_count',
        'hold_time',
        'strategy'
    ];

    public function getActiveAttribute($model)
    {
        return ! $model['is_canceled'];
    }

    public function getProgressAttribute($model)
    {
        /**
         * @var $startAt Carbon
         */
        $startAt = array_get($model, 'start_at');
        /**
         * @var $endAt Carbon
         */
        $endAt = array_get($model, 'end_at');
        $total = $endAt->diffInSeconds($startAt);
        $now = Carbon::now();
        $passed = $now->diffInSeconds($startAt);

        return $this->formatNumber($passed / $total * 100);
    }

    public function getCurrentCycleAttribute($model)
    {
        $now = Carbon::now();
        $cycle = Cycle::where('round_id', $model['id'])
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)
            ->first();

        return $cycle ? (int)$cycle->index : null;
    }

    public function getHoldTimeAttribute($model)
    {
        return array_get($model, 'exchange_account.auto_entry_hold_time', 0);
    }
}