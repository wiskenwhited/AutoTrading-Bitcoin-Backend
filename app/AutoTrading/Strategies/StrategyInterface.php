<?php

namespace App\AutoTrading\Strategies;

use App\Models\Cycle;
use App\Models\ExchangeAccount;
use App\Models\Round;

interface StrategyInterface
{
    public function filterCoinsMatchingRoundCriteria(Round $round, Cycle $cycle, ExchangeAccount $account);

    public function processPurchases(Round $round, Cycle $cycle, ExchangeAccount $account, array $coins);

    public function processExit(Round $round, ExchangeAccount $account);
}