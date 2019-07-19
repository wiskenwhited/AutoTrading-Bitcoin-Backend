<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
    (new Dotenv\Dotenv(__DIR__ . '/../', '.env.heartbeat'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|-------------------------------------------------- ------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->configureMonologUsing(function (\Monolog\Logger $monolog) {
//    $handler = new \Monolog\Handler\StreamHandler(storage_path('logs/lumen.log'));
    $handler = new \Monolog\Handler\NullHandler();
    $handler->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true, true));
    $monolog->pushHandler($handler);

    return $monolog;
});


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

//$app->middleware([
//	\Barryvdh\Cors\HandleCors::class,
//]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'api' => App\Http\Middleware\ApiMiddleware::class,
    'api.auth' => App\Http\Middleware\ApiAuthMiddleware::class,
    'api.admin' => App\Http\Middleware\ApiAdminMiddleware::class,
]);

$app->configure('auth');
$app->configure('database');
$app->configure('cors');
$app->configure('mail');
$app->configure('services');
$app->configure('queue');
$app->configure('api');
$app->configure('broadcasting');
$app->configure('twilio');
$app->configure('flysystem');


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(GrahamCampbell\Flysystem\FlysystemServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
