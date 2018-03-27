<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Queue\SerializesModels;

class Trade extends Model
{
    use SerializesModels, SoftDeletes;

    const STATUS_BUY_ORDER = "Buy-Order";
    const STATUS_BOUGHT = "Bought";
    const STATUS_SELL_ORDER = "Sell-Order";
    const STATUS_SOLD = "Sold";

    protected $fillable = [
        'user_id',
        'exchange_id',
        'exchange_account_id',
        'original_trade_id',
        'order_uuid',
        'parent_trade_id',
        'base_coin_id',
        'target_coin_id',
        'order_type',
        'quantity',
        'quantity_remaining',
        'limit',
        'reserved',
        'reserved_remaining',
        'commission_reserved',
        'commission_reserved_remaining',
        'commission_paid',
        'price',
        'price_per_unit',
        'opened',
        'closed',
        'is_open',
        'sentinel',
        'cancel_initiated',
        'immediate_or_cancel',
        'is_conditional',
        'condition',
        'condition_target',
        'status',
        'gap_bought',

        'current_shrink_differential',
        'target_shrink_differential' .
        'target_percent',

        'is_test'
    ];

    public function newQuery()
    {
        $query = parent::newQuery();


        return $query;
    }

    public function originalTrade()
    {
        return $this->belongsTo(Trade::class, 'original_trade_id');
    }

    public function scopeByOrderUuid($query, $orderUuid)
    {
        return $query->where('order_uuid', $orderUuid);
    }

    public function scopeNotPartial($query)
    {
        return $query->whereNull('parent_trade_id');
    }

    public function scopePartial($query)
    {
        return $query->whereNotNull('parent_trade_id');
    }

    public function scopeByParentTrade($query, $trade)
    {
        if ($trade instanceof Trade) {
            $trade = $trade->id;
        }

        return $query->where('parent_trade_id', $trade);
    }

    public function scopeByUser($query, $user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        return $query->where('user_id', $user);
    }

    public function getIsBuyAttribute()
    {
        return in_array($this->attributes['status'], [self::STATUS_BUY_ORDER, self::STATUS_BOUGHT]);
    }

    public function getIsSellAttribute()
    {
        return in_array($this->attributes['status'], [self::STATUS_SELL_ORDER, self::STATUS_SOLD]);
    }


    public function suggestion()
    {
        //TODO: check if exchange id
        return $this->hasOne(Suggestion::class, 'coin', 'target_coin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function market()
    {
        return $this->hasOne(MarketSummary::class, 'target_coin_id', 'target_coin_id');
    }

    public function coin()
    {
        return $this->hasOne(Coin::class, 'symbol', 'target_coin_id');
    }

    public function exchangeAccount()
    {
        return $this->belongsTo(ExchangeAccount::class);
    }

    public function scopeWithMarket($scope)
    {
        return $scope
            ->select(
                'trades.*',
                'market_summary.ask as market_summary_cpp',
                'market_summary.bid as market_summary_bid'
            )
            ->leftJoin('market_summary', function ($join) {
                $join->on('market_summary.target_coin_id', '=', 'trades.target_coin_id')
                    ->on('market_summary.base_coin_id', '=', 'trades.base_coin_id')
                    ->on('market_summary.exchange_id', '=', 'trades.exchange_id');
            });
    }
}