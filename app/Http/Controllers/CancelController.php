<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Jobs\UpdateTradeOrderJob;
use App\Models\ExchangeAccount;
use App\Models\Trade;
use App\TradingBot\Requests\CancelRequest;
use App\TradingBot\Requests\SellRequest;
use App\TradingBot\TradingBot;
use App\Views\TradeView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CancelController extends ApiController
{
    use HandlesTradingBotResponsesTrait;

    public function post(Request $request, Auth $auth, TradingBot $tradingBot)
    {
        $validator = Validator::make($request->input(), [
            'exchange_account_id' => 'required|exists:exchange_accounts,id',
            'trade_id' => 'required|exists:trades,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = $auth->user();

        $account = ExchangeAccount::find($request->input('exchange_account_id'));
        $trade = Trade::find($request->input('trade_id'));
        if ($account->user_id != $user->id || $trade->user_id != $user->id) {
            return response()->json("Forbidden", 403);
        }
        if ($trade->exchange_id != $account->exchange_id) {
            return response()->json(['trade_id' => 'Incompatible trade and exchange account'], 422);
        }

        $isTest = $request->get('mode') == 'test';
        if (! $isTest) {
            $response = $tradingBot->cancel(new CancelRequest([
            'order_uuid' => $trade->order_uuid,
            'exchange' => $account->exchange_id,'exchange_account_id' => $account->id,
            'key' => $account->key,
            'secret' => $account->secret,
        'user_id' => $user->id
        ]), TradingBot::WAIT);

            if ($error = array_get($response, 'error')) {
                if ($error == 'APIKEY_INVALID') {
                    response()->json("Invalid exchange API credentials", 422);
                }

                return response()->json($error, 500);
            }

            $job = new UpdateTradeOrderJob($trade, $response['trading_bot_request_id']);
            // Update quantities and partial orders if any happened before canceling
            $job->handle($tradingBot);
        }

        return response()->json($trade);
    }
}