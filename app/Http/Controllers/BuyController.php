<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Jobs\UpdateTestTradeJob;
use App\Jobs\UpdateTradeOrderJob;
use App\Models\Coin;
use App\Models\ExchangeAccount;
use App\Models\MarketSummary;
use App\Models\Trade;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\TradingBot;
use App\Views\TradeView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class BuyController extends ApiController
{
    use HandlesTradingBotResponsesTrait;

    public function post(Request $request, Auth $auth, TradingBot $tradingBot)
    {
        $validator = Validator::make($request->input(), [
            'exchange_account_id' => 'required|exists:exchange_accounts,id',
            'base_coin_id' => 'required',
            'target_coin_id' => 'required',
            'quantity' => 'required',
            'rate' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = $auth->user();

        $account = ExchangeAccount::find($request->input('exchange_account_id'));
        if ($account->user_id != $user->id) {
            return response("Forbidden", 403);
        }

        $coin = Coin::whereSymbol($request->get('target_coin_id'))->first();
        $marketSummary = MarketSummary::where('base_coin_id', $request->get('base_coin_id'))
            ->where('target_coin_id', $request->get('target_coin_id'))
            ->where('exchange_id', $account->exchange_id)
            ->first();
        // TODO Handle missing summary
        $startingSd = (double)$marketSummary->bid - (double)$request->get('rate');
        $isTest = $request->get('mode') == 'test';
        if ($isTest) {
            $trade = Trade::create([
                'order_uuid' => Uuid::uuid4()->toString(),
                'exchange_id' => $account->exchange_id,
                'exchange_account_id' => $account->id,
                'base_coin_id' => $request->get('base_coin_id'),
                'target_coin_id' => $request->get('target_coin_id'),
                'user_id' => $user->id,
                'status' => Trade::STATUS_BUY_ORDER,
                'gap_bought' => $coin ? $coin->price_btc : null,
                'quantity' => (double)$request->get('quantity'),
                'price_per_unit' => (double)$request->get('rate'),
                'starting_shrink_differential' => $startingSd,
                'is_test' => $isTest
            ]);
            (new UpdateTestTradeJob($trade))->handle($tradingBot);
        } else {
            $response = $tradingBot->buy(new BuyRequest([
                'exchange' => $account->exchange_id,
                'base' => $request->get('base_coin_id'),
                'strategy' => 'trend',
                'coin' => $request->get('target_coin_id'),
                'quantity' => (float)$request->get('quantity'),
                'rate' => (float)$request->get('rate'),
                'key' => $account->key,
                'secret' => $account->secret
            ]), TradingBot::WAIT);

            // TODO Map responses to human readable errors
            if ($error = array_get($response, 'error')) {
                Log::error("Buy request returned an error", $response);
                if ($error == 'APIKEY_INVALID') {
                    response()->json("Invalid exchange API credentials", 422);
                }

                return response()->json($error, 500);
            }

            $gapBought = null;
            if ($coin) {
                $gapBought = $coin->price_btc;
            }
            $trade = Trade::create(array_merge([
                'exchange_id' => $account->exchange_id,
                'exchange_account_id' => $account->id,
                'base_coin_id' => $request->get('base_coin_id'),
                'target_coin_id' => $request->get('target_coin_id'),
                'user_id' => $user->id,
                'status' => Trade::STATUS_BUY_ORDER,
                'quantity' => (float)$request->get('quantity'),
                'price_per_unit' => (float)$request->get('rate'),
                'starting_shrink_differential' => $startingSd,
                'gap_bought' => $gapBought
            ], array_get($response, 'data', [])));

            dispatch(new UpdateTradeOrderJob($trade, $response['trading_bot_request_id']));
        }

        $view = new TradeView();

        return response()->json($view->render($trade));
    }
}