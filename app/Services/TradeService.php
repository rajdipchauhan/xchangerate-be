<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TradeService
{
    public function getTradesWithCalculatedFields(
        User $user,
        array $page,
        array $sort,
        array $filters = []
    )
    {
        $query = Trade::with('coin', 'user.currencyRate', 'exchangeAccount')
            ->where('trades.user_id', $user->id);

        $boughtStatus = Trade::STATUS_BOUGHT;
        $query->select(
            "trades.*",
            "exchange_accounts.name AS exchange_account_name",
            "ms.ask AS cpp",
            "ms.bid AS highest_bid",
            "c.price_btc AS gap",
            // Calculate profit
            DB::raw("GREATEST(0, (ms.bid - trades.price_per_unit) * trades.quantity) AS profit"),
            // Calculate shrink differential
            DB::raw("@sd:=CASE WHEN trades.starting_shrink_differential > 0 THEN
                (c.price_btc - ms.bid) / trades.starting_shrink_differential * 100
                ELSE
                    null
                END AS shrink_differential"),
            // Calculate target price
            DB::raw("@tp:=trades.price_per_unit * (1 + trades.target_percent/100) AS target_price"),
            // Calculate suggestion
            DB::raw("CASE WHEN trades.status = '$boughtStatus' THEN  
                CASE WHEN trades.target_shrink_differential > 0 
                    AND @sd <= trades.target_shrink_differential THEN
                    'Sell'
                WHEN trades.target_percent > 0 
                    AND @tp > trades.price_per_unit 
                    AND c.price_btc >= @tp THEN
                    'Sell'
                WHEN trades.target_percent > 0 
                    AND @tp < trades.price_per_unit 
                    AND c.price_btc <= @tp THEN
                    'Sell'
                ELSE
                    'Hold'
                END
            ELSE 
                null 
            END AS suggestion")
        )
            ->leftJoin(DB::raw('market_summary AS ms'), function ($join) {
                $join->on('ms.target_coin_id', '=', 'trades.target_coin_id');
                $join->on('ms.base_coin_id', '=', 'trades.base_coin_id');
                $join->on('ms.exchange_id', '=', 'trades.exchange_id');
            })
            ->leftJoin(DB::raw('coins AS c'), function ($join) {
                $join->on('c.symbol', '=', 'trades.target_coin_id');
            })
            ->leftJoin('exchange_accounts', function ($join) {
                $join->on('exchange_accounts.id', '=', 'trades.exchange_account_id');
            });

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply pagination
        if (!is_null($page['limit']) && !is_null($page['offset'])) {
            $query->limit($page['limit'])
                ->offset($page['offset']);
        }

        // Apply sorting
        foreach ($sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        $trades = $query->get();

        $query = Trade::with('exchangeAccount')
            ->where('trades.user_id', $user->id);
        $query = $this->applyFilters($query, $filters);
        $total = $query->count();

        return [$trades, $total];
    }

    protected function applyFilters($query, $filters)
    {
        if ($coin = array_get($filters, 'target_coin_id')) {
            $query->where('trades.target_coin_id', trim($coin));
        }
        if ($exchangeAccountId = array_get($filters, 'exchange_account_id')) {
            $query->where('trades.exchange_account_id', trim($exchangeAccountId));
        }
        if ($exchangeAccountId = array_get($filters, 'exchange_id')) {
            $query->where('trades.exchange_id', trim($exchangeAccountId));
        }
        if ($exchangeAccountName = array_get($filters, 'exchange_account_name')) {
            $query->whereHas('exchangeAccount', function ($query) use ($exchangeAccountName) {
                $query->where('exchange_accounts.name', 'LIKE', "$exchangeAccountName%");
            });
        }
        if ($status = array_get($filters, 'status')) {
            $query->where('trades.status', trim($status));
        }
        $query->where('trades.is_test', array_get($filters, 'is_test') == 'test');

        return $query;
    }
}