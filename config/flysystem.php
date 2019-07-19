<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Flysystem.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => env('FILE_ADAPTER', 'awss3'),//'local',

    /*
    |--------------------------------------------------------------------------
    | Flysystem Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Examples of
    | configuring each supported driver is shown below. You can of course have
    | multiple connections per driver.
    |
    */

    'connections' => [

        'awss3' => [
            'driver'          => 'awss3',
            'key'             => 'AKIAIX5QGUFKM5IZUS6Q',
            'secret'          => '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',
            'bucket'          => 'xchangerate-be',
            'region'          => 'us-east-2',
            'version'         => 'latest',
            // 'bucket_endpoint' => false,
            // 'calculate_md5'   => true,
            // 'scheme'          => 'https',
            // 'endpoint'        => 'your-url',
            // 'prefix'          => 'your-prefix',
            // 'visibility'      => 'public',
            // 'pirate'          => false,
            // 'eventable'       => true,
            // 'cache'           => 'foo'
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Flysystem Cache
    |--------------------------------------------------------------------------
    |
    | Here are each of the cache configurations setup for your application.
    | There are currently two drivers: illuminate and adapter. Examples of
    | configuration are included. You can of course have multiple connections
    | per driver as shown.
    |
    */

    'cache' => [

        'foo' => [
            'driver'    => 'illuminate',
            'connector' => null, // null means use default driver
            'key'       => 'foo',
            // 'ttl'       => 300
        ],

        'bar' => [
            'driver'    => 'illuminate',
            'connector' => 'redis', // config/cache.php
            'key'       => 'bar',
            'ttl'       => 600,
        ],

        'adapter' => [
            'driver'  => 'adapter',
            'adapter' => 'local', // as defined in connections
            'file'    => 'flysystem.json',
            'ttl'     => 600,
        ],

    ],

];
