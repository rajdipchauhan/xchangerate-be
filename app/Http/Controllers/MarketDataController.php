<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Models\MarketSummary;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\TradingBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class MarketDataController extends ApiController
{

    private $marketOrderService;

    public function __construct(MarketOrderService $marketOrderService)
    {
        $this->marketOrderService = $marketOrderService;
    }

    public function marketOrder($exchange, $coin)
    {
        $marketOrder = $this->marketOrderService->retrieveMarketOrder($exchange, $coin);

        return response()->json([
            'data' => $marketOrder ? $marketOrder->toArray() : null
        ]);
    }
    public function marketOrderSell($exchange, $coin)
    {
        $marketOrder = $this->marketOrderService->retrieveMarketOrder($exchange, $coin, true);

        return response()->json([
            'data' => $marketOrder ? $marketOrder->toArray() : null
        ]);
    }
    
    public function marketSummary($exchange)
    {
        $marketOrder = MarketSummary::whereExchangeId($exchange)->get();

        return response()->json([
            'data' => $marketOrder
        ]);
    }
}