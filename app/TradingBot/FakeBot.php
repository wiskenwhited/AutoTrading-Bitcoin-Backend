<?php

namespace App\TradingBot;

use App\TradingBot\Models\FakeOrder;
use App\TradingBot\Requests\AbstractTradingBotRequest;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\Requests\CancelRequest;
use App\TradingBot\Requests\OrderStatusRequest;
use App\TradingBot\Requests\SellRequest;
use Ramsey\Uuid\Uuid;

class FakeBot
{
    public function fake(AbstractTradingBotRequest $request)
    {
        switch (get_class($request)) {
            case BuyRequest::class:
                return $this->buy($request);
            case SellRequest::class:
                return $this->sell($request);
            case CancelRequest::class:
                return $this->cancel($request);
            case OrderStatusRequest::class:
                return $this->orderStatus($request);
        }
    }

    protected function buy(BuyRequest $request)
    {
        $data = $request->getData();
        $fakeOrder = FakeOrder::create([
            'order_uuid' => Uuid::uuid4()->toString(),
            'order_type' => 'LIMIT_BUY',
            'quantity' => array_get($data, 'quantity'),
            'quantity_remaining' => array_get($data, 'quantity'),
            'is_open' => true,
            // ~33% chance order will be left open so canceling can be tested
            // TODO See if there's a better way to do this without checking environment
            'leave_open' => env('APP_ENV') == 'testing' ? false : (rand(1, 3) == 3)
        ]);

        return $this->response($fakeOrder->toArray());
    }

    protected function sell(SellRequest $request)
    {
        $data = $request->getData();
        $fakeOrder = FakeOrder::create([
            'order_uuid' => Uuid::uuid4()->toString(),
            'order_type' => 'LIMIT_SELL',
            'quantity' => array_get($data, 'quantity'),
            'is_open' => true,
            // ~33% chance order will be left open so canceling can be tested
            // TODO See if there's a better way to do this without checking environment
            'leave_open' => env('APP_ENV') == 'testing' ? false : (rand(1, 3) == 3)
        ]);

        return $this->response($fakeOrder->toArray());
    }

    protected function cancel(CancelRequest $request)
    {
        $data = $request->getData();
        $fakeOrder = FakeOrder::find(array_get($data, 'order_uuid'));
        $fakeOrder->is_open = false;
        $fakeOrder->cancel_initiated = true;
        $fakeOrder->save();

        return $this->response($fakeOrder->toArray());
    }

    protected function orderStatus(OrderStatusRequest $request)
    {
        $data = $request->getData();
        $fakeOrder = FakeOrder::find(array_get($data, 'order_uuid'));
        // ~33% chance to fill order completely
        $shouldFillOrder = rand(1, 3) == 3;
        // ~20% chance to leave order idle
        $shouldLetIdle = $fakeOrder->leave_open || rand(1,5) == 5;
        if ($shouldFillOrder) {
            $fakeOrder->quantity_remaining = 0;
            $fakeOrder->is_open = false;
        } elseif (! $shouldLetIdle) {
            $diff = ($fakeOrder->quantity - $fakeOrder->quantity_remaining) * 0.3;
            $fakeOrder->quantity_remaining -= $diff;
        }
        $fakeOrder->save();

        return $this->response($fakeOrder->toArray());
    }

    protected function response($data)
    {
        return [
            'job_id' => 42,
            'job_status' => 'InProgress',
            'data' => $data,
            'err' => ''
        ];
    }
}