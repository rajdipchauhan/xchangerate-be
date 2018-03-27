<?php

namespace App\Views;

use App\Models\Trade;
use Illuminate\Support\Collection;

class TradeView extends AbstractView
{
    protected $fields = [
        'id',
        'order_uuid',
        'placed_at',
        'base_coin_id',
        'target_coin_id',
        'exchange_id',
        'exchange_account_id',
        'exchange_account_name',
        'quantity',
        'price_bought',
        'cpp',
        'highest_bid',
        'gap',
        'profit',
        'profit_percent',
        'profit_local_currency',
        'status',
        'status_name',
        'shrink_differential',
        'target_percent',
        'target_price',
        'target_shrink_differential',
        'suggestion',
        'is_test'
    ];

    protected $map = [
        'created_at' => 'placed_at',
        'price_per_unit' => 'price_bought',
    ];

    protected $calculated = [
        'status_name',
        'exchange_name',
        'profit_local_currency',
        'profit_percent'
    ];

    /**
     * @param $model
     * @return mixed|null
     */
    public function getStatusNameAttribute($model)
    {
        switch ($model['status']) {
            case Trade::STATUS_BOUGHT:
                return "BOUGHT";
            case Trade::STATUS_BUY_ORDER:
                return "BUY ORDER";
            case Trade::STATUS_SOLD:
                return "SOLD";
            case Trade::STATUS_SELL_ORDER:
                return "SELL ORDER";
        }
    }

    /**
     * @param $model
     * @return mixed|null
     */
    public function getProfitLocalCurrencyAttribute($model)
    {
        $coinPriceBtc = (double)array_get($model, 'coin.price_btc');
        $coinPriceUsd = (double)array_get($model, 'coin.price_usd');
        $rate = (double)array_get($model, 'user.currencyRate.rate');
        if (! $coinPriceBtc) {
            return null;
        }

        return array_get($model, 'profit', 0) * ($coinPriceUsd / $coinPriceBtc) * $rate;
    }

    public function getProfitPercentAttribute($model)
    {
        $priceBought = (double)array_get($model, 'price_per_unit', 0);
        $quantity = (double)array_get($model, 'quantity', 0);
        $totalBought = $priceBought * $quantity;
        if ($totalBought) {
            return array_get($model, 'profit', 0) / $totalBought * 100;
        }

        return 0;
    }
}