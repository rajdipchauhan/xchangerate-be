<?php

namespace App\Jobs;

use App\Models\TradingBotJob;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;

class MarketSummaryJob extends Job
{
    /**
     * @var
     */
    private $marketOrderService;

    /**
     * MarketSummaryJob constructor.
     * @param MarketOrderService $tradingBotJob
     */
    public function __construct()
    {
    }

    public function handle(MarketOrderService $marketOrderService)
    {

        $marketOrderService->updateMarketSummary();
    }
}