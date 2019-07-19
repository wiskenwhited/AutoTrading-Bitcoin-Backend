<?php

return [

    'heartbeat' => [
        'version' => env('FE_VERSION')
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET')
    ],

    'coinmarketcap' => [
        'url' => 'https://api.coinmarketcap.com/v1/'
    ],

    'openexchangerates' => [
        'url' => 'https://openexchangerates.org/api/',
        'app_id' => env('OPENEXCHANGERATES_APP_ID')
    ],
    'marketorder' => [
        'bitfinex_url' => 'https://api.bitfinex.com/v1/book/{COIN}BTC?{TYPE}&group=1',
        'bittrex_url' => 'https://bittrex.com/api/v1.1/public/getorderbook?market=BTC-{COIN}&type={TYPE}',
        'bittrex_summary_url' => 'https://bittrex.com/api/v1.1/public/getmarketsummaries',
    ],
    'trading_bot' => [
        'url' => env('TRADING_BOT_API_URL')
    ],

    'loggly' => [
        'token' => env('LOGGLY_TOKEN')
    ],
    'coinpayments' => [
        'private_key' => env('COINPAYMENT_PRIVATE_KEY', '1f300B835b474198eBAF6b67a605E315786847e730CfC3F6f4b832CC6c2C4ab4'),
        'public_key' => env('COINPAYMENT_PUBLIC_KEY', '9c957f2bbced6d75e37ff37a4a8824175a73ba8b5562055a45674380399f86b1'),
        'merchant_id' => env('MERCHANT_ID', '6e9d6708c66c12a476c0bcc993f49e41'),
        'ipn_secret' => env('IPN_SECRET', '%12k%$'),
    ]
];
