<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country',
        'city',
        'phone',
        'currency',
        // Settings
        'entry_frugality_ratio',
        'entry_price_relativity_ratio',
        'entry_notified_by_email',
        'entry_notified_by_sms',
        'entry_is_auto_trading',
        'exit_target',
        'exit_shrink_differential',
        'exit_option',
        'exit_notified_by_email',
        'exit_notified_by_sms',
        'exit_is_auto_trading',
        'withdrawal_capital_balance',
        'withdrawal_capital_balance_currency',
        'withdrawal_value',
        'withdrawal_value_coin',
        'withdrawal_address',
        'withdrawal_notified_by_email',
        'withdrawal_notified_by_sms',
        'withdrawal_is_auto_trading',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'verification_link'
    ];

    public function currencyRate()
    {
        return $this->belongsTo(CurrencyRate::class, 'currency', 'target');
    }


    public function total_profit($exchange)
    {
        $total_flat = $total_btc = 0;
        $trades = Trade::whereExchangeId($exchange)->whereUserId($this->id)->where('order_type', Trade::STATUS_BOUGHT)->get();
        if ($trades->count() > 0) {
            $coins_bought = array_pluck($trades, 'target_coin_id');
            $coins = Coin::whereIn('id', $coins_bought)->get();
            foreach ($trades as $trade) {
                $coin = $coins->where('id', $trade->target_coin_id)->first();
                if ($coin->price_btc > $trade->price) {
                    $total_btc += ($coin->price_btc * $trade->quantity);
                }
            }

            $currency_rate = CurrencyRate::where('base', $this->currency)->where('target', 'BTC')->first();
            $total_flat = $total_btc / $currency_rate->rate;
        }

        return [
            'total_profit' => $total_btc,
            'total_profit_currency' => 'BTC',
            'total_flat' => $total_flat,
            'total_flat_currency' => $this->currency,
        ];
    }

    public function profit_realized($exchange)
    {
        //Profit Realised in BTC & CURRENCY would show - Active Sum (PriceSold) x (QtySold)

        $total_flat = $total_btc = 0;
//        $trades = Trade::whereExchangeId($exchange)->whereUserId($this->id)->where('order_type', Trade::STATUS_SELL_CLOSED)->get();
//        if ($trades->count() > 0) {
//            $coins_bought = array_pluck($trades, 'target_coin_id');
//            $coins = Coin::whereIn('id', $coins_bought)->get();
//            foreach ($trades as $trade) {
//                $coin = $coins->where('id', $trade->target_coin_id)->first();
//                if ($coin->price_btc > $trade->price) {
//                    $total_btc += ($coin->price_btc * $trade->quantity);
//                }
//            }
//
//            $currency_rate = CurrencyRate::where('base', $this->currency)->where('target', 'BTC')->first();
//            $total_flat = $currency_rate->rate * $total_btc;
//        }

        return [
            'realized_profit' => $total_btc,
            'realized_profit_currency' => 'BTC',
            'realized_flat' => $total_flat,
            'realized_flat_currency' => $this->currency,
        ];
    }
}
