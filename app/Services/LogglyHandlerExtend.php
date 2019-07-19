<?php


namespace App\Services;


class LogglyHandlerExtend extends \Monolog\Handler\LogglyHandler
{

    protected function getDefaultFormatter()
    {
        return new LogglyFormatterExtend();
    }
}
