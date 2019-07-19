<?php

namespace App\TradingBot\Requests;

abstract class AbstractTradingBotRequest
{
    const GLOBAL_CHANNEL = 'global';
    const USER_CHANNEL = 'user';
    protected $params = [];
    protected $data = [];
    protected $channel;

    public function __construct(array $params, $channel = self::GLOBAL_CHANNEL)
    {
        $this->data = array_filter(
            array_only($params, $this->params),
            function ($datum) {
                return ! is_null($datum);
            }
        );
        $this->channel = $channel;
    }

    /**
     * Fetches request params formatted as key value pairs ready for http client.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}