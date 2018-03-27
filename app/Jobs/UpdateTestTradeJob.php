<?php

namespace App\Jobs;

use App\Models\Suggestion;
use App\Models\Trade;
use App\TradingBot\Requests\ExchangeSuggestionsRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTestTradeJob extends Job
{
    /**
     * @var Trade
     */
    private $trade;

    /**
     * ProcessTradingBotResponseJob constructor.
     * @param Trade $trade
     */
    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    public function handle(TradingBot $tradingBot)
    {
        $suggestion = Suggestion::where('exchange', $this->trade->exchange_id)
            ->where('coin', $this->trade->target_coin_id)
            ->first();

        $askPrice = object_get($suggestion, 'cpp', null);
        if (
            ! is_null($askPrice) && (
                $this->trade->status == Trade::STATUS_BUY_ORDER &&
                (float)$this->trade->price >= (float)$askPrice ||
                $this->trade->status == Trade::STATUS_SELL_ORDER &&
                (float)$this->trade->price <= (float)$askPrice
            )
        ) {
            $this->trade->status = $this->trade->status == Trade::STATUS_BUY_ORDER ?
                Trade::STATUS_BOUGHT : Trade::STATUS_SOLD;
            $this->trade->save();
        } else {
            $job = new self($this->trade);
            $job->delay(Carbon::now()->addSecond(10));
            dispatch($job);
        }
    }
}