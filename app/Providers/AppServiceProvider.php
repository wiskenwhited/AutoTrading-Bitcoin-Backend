<?php

namespace App\Providers;

use App\Auth\Auth;
use App\Services\CurrencyService;
use App\Services\LogglyHandlerExtend;
use App\Services\MarketOrderService;
use App\TradingBot\FakeBot;
use App\TradingBot\JobProcessor;
use App\TradingBot\TradingBot;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\LogglyHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLogHandlers();
        $this->registerListeners();
        //$this->registerBugsnag();
        $this->registerTradingBot();
        $this->registerCurrencyService();
        $this->registerAuth();
    }

    protected function registerLogHandlers()
    {
        if ($this->app->environment('local')) {
            $this->app['log']->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        } elseif ($this->app->environment('staging', 'production')) {
            $handler = new LogglyHandlerExtend(config('services.loggly.token'), Logger::INFO);
            $handler->addTag('xchangerate');
            $handler->addTag('php');
            $handler->addTag($this->app->environment());
            $this->app['log']->pushHandler($handler);
        }
    }

    protected function registerListeners()
    {
        /**
         * @var $dispatcher Dispatcher
         */
        $dispatcher = $this->app['events'];
    }

    protected function registerBugsnag()
    {
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->extend(\Psr\Log\LoggerInterface::class, function ($logger, $app) {
            return new \Bugsnag\BugsnagLaravel\MultiLogger([$logger, $app['bugsnag.logger']]);
        });
    }

    protected function registerTradingBot()
    {
        $this->app->singleton(TradingBot::class, function ($app) {
            return new TradingBot(new FakeBot());
        });
        $this->app->alias(TradingBot::class, 'trading_bot');
        $this->app->bind(JobProcessor::class, function ($app) {
            return new JobProcessor($app->make(TradingBot::class), $app['log']);
        });
        $this->app->alias(JobProcessor::class, 'trading_bot_job_processor');
    }

    protected function registerCurrencyService()
    {
        $this->app->bind(CurrencyService::class, function ($app) {
            return new CurrencyService(
                new Client([
                    'base_uri' => config('services.coinmarketcap.url')
                ]),
                new Client([
                    'base_uri' => config('services.openexchangerates.url'),
                    'query' => [
                        'app_id' => config('services.openexchangerates.app_id')
                    ]
                ]),
                $app['log']
            );
        });
        $this->app->alias(TradingBot::class, 'currency_service');
    }

    protected function registerAuth()
    {
        $this->app->singleton(Auth::class);
    }
}
