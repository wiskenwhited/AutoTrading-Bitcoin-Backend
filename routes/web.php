<?php

/**
 * @var $app \Laravel\Lumen\Application
 */
$app->get('/test', 'HomeController@pageWeb');

$app->get('/health', function () {
    return response()->json(['status' => 'ok']);
});

$app->group(
    [
        'prefix' => '/api',
    ],
    function ($app) {

        $app->post('/coinpayment', 'CoinPaymentController@retrieve');
        $app->get('/coinpayment', 'CoinPaymentController@retrieve');
    }
);

$app->group(
    [
        'prefix' => '/api',
        'middleware' => ['api']
    ],
    function ($app) {
        /**
         * @var $app \Laravel\Lumen\Application
         */
        // Auth
        $app->post('auth/user-registration', 'Auth\AuthController@postRegistration');
        $app->post('auth/login', 'Auth\AuthController@login');
        $app->post('auth/2fa', 'Auth\AuthController@fa2');

        $app->get('verify/{verification_code}', 'Auth\AuthController@verify');
        $app->post('auth/reset-password', 'Auth\ResetPasswordController@postReset');
        $app->post('auth/confirm-reset-password', 'Auth\ResetPasswordController@postConfirmation');

        $app->get('currencies', 'CurrencyController@index');
        $app->get('countries', 'CountryController@index');
        $app->get('cities', 'CityController@index');

        $app->get('heartbeat', 'HeartbeatController@index');

        $app->get('/blog', 'BlogController@index');
        $app->get('/article', 'ArticleController@index');
        $app->get('/article/{slug}', 'ArticleController@show');
        $app->get('/faq', 'FAQController@index');


        // Deprecated
        $app->get('country', 'PublicController@country');
        $app->get('city', 'PublicController@city');

        $app->post('/contact-us', 'ContactController@send');
        $app->get('/pricing', 'BillingController@pricing');

    }
);

$app->group(
    [
        'prefix' => '/api',
        'middleware' => ['api', 'api.auth']
    ],
    function ($app) {
        /**
         * @var $app \Laravel\Lumen\Application
         */
        // Auth
        $app->post('auth/logout', 'Auth\AuthController@logout');

        // Bot
        $app->get('suggestions', 'SuggestionController@index');
        $app->post('buy', 'BuyController@post');
        $app->post('sell', 'SellController@post');
        $app->post('cancel', 'CancelController@post');
        $app->get('jobs/{id}', 'JobController@show');

        $app->get('market-order/{exchange}/{coin}', 'MarketDataController@marketOrder');
        $app->get('market-order/sell/{exchange}/{coin}', 'MarketDataController@marketOrderSell');
        $app->get('market-summary/{exchange}', 'MarketDataController@marketSummary');

        // Data
        $app->get('coins', 'CoinController@index');
        $app->get('coins/{id}', 'CoinController@show');
        $app->get('coins/convert/{currencyFrom}/{currencyTo}', 'CoinController@convert');

        $app->get('currency-rates', 'CurrencyRateController@index');

        $app->get('exchanges', 'ExchangeController@index');
        $app->get('exchanges/{id}', 'ExchangeController@show');

        $app->get('trades', 'TradeController@index');
        $app->patch('trades/{id}', 'TradeController@patch');
        $app->delete('trades/{id}', 'TradeController@delete');
        $app->get('trades/total/{exchange}', 'TradeController@total');

        $app->get('exchange-accounts', 'ExchangeAccountController@index');
        $app->get('exchange-accounts/{id}', 'ExchangeAccountController@show');
        $app->post('exchange-accounts', 'ExchangeAccountController@create');
        $app->patch('exchange-accounts/{id}', 'ExchangeAccountController@update');
        $app->delete('exchange-accounts/{id}', 'ExchangeAccountController@delete');

        $app->get('users/current', 'UserController@show');
        $app->put('users/current', 'UserController@update');
        $app->get('users/qr', 'UserController@qr');
        $app->post('users/qr', 'UserController@saveQr');
        $app->post('users/2fa', 'UserController@save2FA');

        //Referrals
        $app->get('referrals/list', 'ReferralController@index');
        $app->get('referrals/url', 'ReferralController@getReferralUrl');
        $app->post('referrals/wallet-id', 'UserController@saveWallet');

        $app->get('users/current/settings', 'SettingController@show');
        $app->patch('users/current/settings', 'SettingController@update');
        $app->post('watchlist', 'WatchlistController@store');
        $app->post('watchlist/sell', 'WatchlistController@storeSell');
        $app->put('watchlist/{id}', 'WatchlistController@update');
        $app->get('watchlist/{id}/history', 'WatchlistController@showHistory');
        $app->get('watchlist/{id}/sell/rule', 'WatchlistController@showSellRule');
        $app->put('watchlist/{id}/sell', 'WatchlistController@updateSell');
        $app->put('watchlist/{id}/rule', 'WatchlistController@updateRule');
        $app->get('watchlist/{exchange}', 'WatchlistController@index');
        $app->delete('watchlist/{id}', 'WatchlistController@delete');
//        $app->get('watchlist/sendingmail/{id}', 'WatchlistController@sendingmail');

        $app->get('round/{exchange_account_id}', 'RoundController@status');
        $app->post('round/start', 'RoundController@start');
        $app->post('round/stop', 'RoundController@stop');

        $app->post('billing/scratch-code', 'BillingController@scratch');

        $app->get('billing/package-data', 'BillingController@packageData');
        $app->get('billing/user-package', 'BillingController@userPackage');
        $app->get('billing/check-test-activation', 'BillingController@checkTestPackage');
        $app->get('billing/check-live-activation', 'BillingController@checkLivePackage');
        $app->post('billing/activate-test-mode', 'BillingController@activateTestMode');
        $app->post('billing/activate-live-mode', 'BillingController@activateLiveMode');
        $app->get('billing/history', 'BillingController@history');

        $app->post('billing/test-purchase', 'BillingController@testPurchase');

    }
);


$app->group(
    [
        'prefix' => '/api/admin',
        'middleware' => ['api', 'api.admin']
    ],
    function ($app) {
        /**
         * @var $app \Laravel\Lumen\Application
         */

        $app->post('/file', 'Admin\FileController@store');
        $app->get('/files', 'Admin\FileController@index');
        $app->get('/files/{folder}', 'Admin\FileController@index');

        $app->get('/blog', 'Admin\BlogController@index');
        $app->post('/blog', 'Admin\BlogController@store');
        $app->get('/blog/{id}', 'Admin\BlogController@show');
        $app->put('/blog/{id}', 'Admin\BlogController@update');
        $app->delete('/blog/{id}', 'Admin\BlogController@destroy');
        $app->put('/blog/{id}/publish', 'Admin\BlogController@publish');
        $app->put('/blog/{id}/unpublish', 'Admin\BlogController@unpublish');

        $app->get('/article', 'Admin\ArticleController@index');
        $app->get('/article/{id}', 'Admin\ArticleController@show');
        $app->put('/article/{id}', 'Admin\ArticleController@update');

        $app->get('/faq', 'Admin\FAQController@index');
        $app->post('/faq', 'Admin\FAQController@store');
        $app->get('/faq/{id}', 'Admin\FAQController@show');
        $app->put('/faq/{id}', 'Admin\FAQController@update');
        $app->delete('/faq/{id}', 'Admin\FAQController@destroy');
        $app->put('/faq/{id}/publish', 'Admin\FAQController@publish');
        $app->put('/faq/{id}/unpublish', 'Admin\FAQController@unpublish');

        $app->get('/package/list', 'Admin\BillingPackageController@index');
        $app->put('/package/feature', 'Admin\BillingPackageController@updateFeature');
        $app->put('/package/{id}', 'Admin\BillingPackageController@update');

        $app->get('/contacts', 'Admin\ContactController@index');
        $app->post('/contact', 'Admin\ContactController@store');


        $app->get('referrals/users', 'ReferralController@adminUsersList');
        $app->get('referrals/users/paid', 'ReferralController@adminUsersListPaid');
        $app->post('referrals/users', 'ReferralController@adminUsersPay');

    }
);