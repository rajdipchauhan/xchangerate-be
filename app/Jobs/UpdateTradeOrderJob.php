<?php

namespace App\Jobs;

use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\TradingBot\Requests\OrderStatusRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateTradeOrderJob extends Job
{
    const JOB_DELAY_SECONDS = 2;
    const TEST_JOB_DELAY_SECONDS = 5;

    private $tradingBotRequestId;

    /**
     * @var Trade
     */
    private $trade;

    /**
     * ProcessTradingBotResponseJob constructor.
     * @param Trade $trade
     * @param $tradingBotRequestId
     */
    public function __construct(Trade $trade, $tradingBotRequestId)
    {
        $this->trade = $trade;
        $this->tradingBotRequestId = $tradingBotRequestId;
    }

    public function handle(TradingBot $tradingBot)
    {
        $response = $tradingBot->getTradingBotRequestResponse($this->tradingBotRequestId);
        // TODO Temp solution, we should not stop job on error, error should be resolved on bot side
        // If response results in an error, we stop
        if (array_get($response, 'error')) {
            return;
        }

        $data = array_get($response, 'data');
        if (! $data) {
            Log::error("No data from trading bot received for order status", [
                'trade_id' => $this->trade->id,
                'response_data' => $response
            ]);
            $job = new UpdateTradeOrderJob($this->trade, $this->tradingBotRequestId);
            $job->delay(Carbon::now()->addSeconds(10));
            dispatch($job);

            return;
        }

        $orderIsOpen = array_get($response, 'is_open', true);
        $orderIsCanceled = array_get($data, 'cancel_initiated', true);

        // If there is a change in bought or sold quantity we update trade records
        if ($this->trade->quantity != (float)$data['quantity_remaining']) {
            $this->updateTradeRecords($data);
        }

        $originalTrade = null;
        if ($this->trade->is_sell) {
            $originalTrade = $this->trade->originalTrade;
            $originalTrade->quantity = $this->trade->quantity;
            $originalTrade->save();
        }

        if ($orderIsOpen && ! $orderIsCanceled) {
            $job = new UpdateTradeOrderJob($this->trade, $this->tradingBotRequestId);
            $job->delay(Carbon::now()->addSeconds(
                $this->trade->is_test ? self::TEST_JOB_DELAY_SECONDS : self::JOB_DELAY_SECONDS
            ));
            dispatch($job);
        } else {
            //$this->trade->delete();
            if ($originalTrade) {
                //$originalTrade->delete();
            }
        }
    }

    protected function updateTradeRecords($data)
    {
        $startQuantity = (float)$data['quantity'];
        $remainingQuantity = (float)$data['quantity_remaining'];
        $data['quantity'] = $remainingQuantity;
        $this->trade->update($data);

        // We make sure trade data is copied to new trade record
        $data = array_merge($data, array_only($this->trade->getAttributes(), [
            'user_id',
            'exchange_id',
            'order_uuid',
            'base_coin_id',
            'target_coin_id',
            'order_type',
            'limit',
            'reserved',
            'reserved_remaining',
            'commission_reserved',
            'commission_reserved_remaining',
            'commission_paid',
            'price',
            'price_per_unit',
            'sentinel',
            'gap_bought'
        ]));
        $data['quantity'] = $startQuantity - $remainingQuantity;
        $partialTrade = Trade::byParentTrade($this->trade)->first();
        if (! $partialTrade) {
            if ($this->trade->is_buy) {
                $data['status'] = Trade::STATUS_BOUGHT;
            } elseif ($this->trade->is_sell) {
                $data['status'] = Trade::STATUS_SOLD;
            }
            $data['parent_trade_id'] = $this->trade->id;
            $partialTrade = Trade::create($data);
        } else {
            $partialTrade->update($data);
        }
    }
}