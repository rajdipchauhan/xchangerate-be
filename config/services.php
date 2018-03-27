<?php

return [

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
    ]
];
