<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class Watchlist extends Model
{
    protected $table = 'watchlist';
    
    protected $fillable =
        [
            'user_id',
            'interval',
            'exchange',
            'coin',
            'target',
            'exchange_trend',
            'market_cap',
            'btc_impact',
            'impact_1hr',
            'gap',
            'cpp',
            'prr',
            'base',
            'lowest_ask',
            'highest_bid',
            'btc_liquidity_bought',
            'btc_liquidity_sold',
            'target_score',
            'exchange_trend_score',
            'impact_1hr_change_score',
            'btc_impact_score',
            'btc_liquidity_score',
            'market_cap_score',
            'overall_score',
            'last_check',
            'sms',
            'email',
            'execute',
        ];
}
