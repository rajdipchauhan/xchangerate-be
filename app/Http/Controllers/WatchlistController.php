<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Models\Watchlist;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WatchlistController extends ApiController
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function index(Auth $auth, Request $request, $exchange)
    {
        $query = Watchlist::getQuery()->where('user_id', $auth->user()->id)->where('exchange', $exchange);
        $total = $query->count();

        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $watchlists = $query->get();

        return response()->json([
            'data' => $watchlists,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function show($id)
    {

    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'exchange' => 'required',
            'coin' => 'required|string',
            'interval' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $query = Suggestion::getQuery()->where('coin', $request->coin)->where('exchange', $request->exchange);
        $total = $query->count();
        if ($total == 0) {
            return response()->json('No suggestions data found for given input.', 422);
        }

        try {
            $suggestion = $query->first();
            $watchlist = new Watchlist();
            $user = $this->auth->user();
            $watchlist->user_id = (string)$user->id;
            $watchlist->interval = $request->interval;
            $watchlist->exchange = $suggestion->exchange;
            $watchlist->coin = $suggestion->coin;
            $watchlist->target = $suggestion->target;
            $watchlist->exchange_trend = $suggestion->exchange_trend;
            $watchlist->btc_impact = $suggestion->btc_impact;
            $watchlist->impact_1hr = $suggestion->impact_1hr;
            $watchlist->gap = $suggestion->gap;
            $watchlist->cpp = $suggestion->cpp;
            $watchlist->prr = $suggestion->prr;
            $watchlist->btc_liquidity_bought = $suggestion->btc_liquidity_bought;
            $watchlist->btc_liquidity_sold = $suggestion->btc_liquidity_sold;
            $watchlist->market_cap = $suggestion->market_cap;
            $watchlist->base = $suggestion->base;
            $watchlist->lowest_ask = $suggestion->lowest_ask;
            $watchlist->highest_bid = $suggestion->highest_bid;
            $watchlist->target_score = $suggestion->target_score;
            $watchlist->exchange_trend_score = $suggestion->exchange_trend_score;
            $watchlist->impact_1hr_change_score = $suggestion->impact_1hr_change_score;
            $watchlist->btc_impact_score = $suggestion->btc_impact_score;
            $watchlist->btc_liquidity_score = $suggestion->btc_liquidity_score;
            $watchlist->market_cap_score = $suggestion->market_cap_score;
            $watchlist->overall_score = $suggestion->overall_score;
            $watchlist->sms = $user->entry_notified_by_sms;
            $watchlist->email = $user->entry_notified_by_email;
            $watchlist->execute = $user->entry_is_auto_trading;
            $watchlist->created_at = Carbon::now();
            $watchlist->save();

            return response()->json(['data' => $watchlist]);
        } catch (\Exception $ex) {
            Log::error($ex);
            return response()->json($ex->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $watchlist = Watchlist::where('id', $id)->first();
        if (!$watchlist) {
            return response()->json('No watchlist found for given input.', 422);
        }

        if ($request->get('email') == '' && $request->get('sms') == '' && $request->get('execute') == '') {
            return response()->json('Atleast one parameter required amount Email,SMS or Execute to update data.', 422);
        }

        $watchlist->fill(array_merge($request->input(), ['id' => $id]));
        $watchlist->save();
        return response()->json($watchlist);
    }

    public function delete($id)
    {

    }
}
