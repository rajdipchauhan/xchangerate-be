<?php

namespace App\Http\Controllers;

use App\Auth\Auth;
use App\Http\Controllers\Traits\HandlesTradingBotResponsesTrait;
use App\Models\Trade;
use App\Services\TradeService;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\TradingBot;
use App\Views\TradeView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TradeController extends ApiController
{
    public function index(Auth $auth, Request $request, TradeService $tradeService)
    {
        $filters = $this->getFilterData($request);
        $filters['is_test'] = $request->get('mode') == 'test';
        $view = new TradeView();
        list($trades, $total) = $tradeService->getTradesWithCalculatedFields(
            $auth->user(),
            $this->getPaginationData($request),
            $this->getSortingData($request, $view),
            $filters
        );

        return response()->json([
            'data' => $view->render($trades),
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }

    public function patch($id, Request $request, Auth $auth)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->user_id != $auth->user()->id) {
            return response()->json("Forbidden", 403);
        }

        if ($request->input('target_shrink_differential')) {
            $trade->target_shrink_differential = $request->input('target_shrink_differential');
            $trade->target_percent = null;
        } elseif ($request->input('target_price')) {
            $trade->target_shrink_differential =null;
            $trade->target_percent = $request->input('target_price');
        }

        $trade->save();
        $producedTrade = Trade::byParentTrade($id)->first();
        if ($producedTrade) {
            $producedTrade->target_shrink_differential = $trade->target_shrink_differential;
            $producedTrade->target_percent = $trade->target_percent;
            $producedTrade->save();
        }
        $view = new TradeView();

        return response()->json($view->render($trade));
    }

    public function total(Auth $auth, $exchange)
    {
        $total_profit = $auth->user()->total_profit($exchange);
        $profit_realized = $auth->user()->profit_realized($exchange);

        return response()->json(array_merge($total_profit, $profit_realized));
    }

    public function delete($id)
    {
        $trade = Trade::findOrFail($id);
        $trade->delete();

        return response()->json([], 200);
    }
}