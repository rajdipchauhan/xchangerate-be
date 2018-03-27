<?php

/**
 * @var $app \Laravel\Lumen\Application
 */
$app->get('/', 'HomeController@pageWeb');

$app->get('/health', function () {
    return response()->json(['status' => 'ok']);
});

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
        $app->get('verify/{verification_code}', 'Auth\AuthController@verify');

        $app->get('currencies', 'CurrencyController@index');
        $app->get('countries', 'CountryController@index');
        $app->get('cities', 'CityController@index');

        // Deprecated
        $app->get('country', 'PublicController@country');
        $app->get('city', 'PublicController@city');
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
        $app->put('exchange-accounts/{id}', 'ExchangeAccountController@update');
        $app->delete('exchange-accounts/{id}', 'ExchangeAccountController@delete');

        $app->get('users/current', 'UserController@show');
        $app->put('users/current', 'UserController@update');
        $app->get('users/current/settings', 'SettingController@show');
        $app->put('users/current/settings', 'SettingController@update');
        $app->post('watchlist', 'WatchlistController@create');
        $app->put('watchlist/{id}', 'WatchlistController@update'); 
        $app->get('watchlist/{exchange}', 'WatchlistController@index');
        $app->get('paid-features', 'PaidFeatureController@paidfeatures');
        $app->get('pay-info', 'PaidFeatureController@payinfo');
        $app->get('billing-history', 'PaidFeatureController@billinghistory');
    }
);
