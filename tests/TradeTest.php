<?php

use App\Models\Coin;
use App\Models\Exchange;
use App\Models\ExchangeAccount;
use App\Models\MarketSummary;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TradeTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testTradesEndpoint()
    {
        $user = factory(User::class)->create();
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => 'bittrex'
        ]);
        $baseCoin = factory(Coin::class)->create([
            'id' => 'bitcoin',
            'symbol' => 'BTC'
        ]);
        $targetCoin = factory(Coin::class)->create([
            'id' => 'pink',
            'symbol' => 'PINK'
        ]);
        $marketSummary = factory(MarketSummary::class)->create([
            'exchange_id' => $exchange->id,
            'base_coin_id' => $baseCoin->id,
            'target_coin_id' => $targetCoin->id,
            'bid' => 0.00002220,
            'ask' => 0.00002218
        ]);
        $trade = factory(Trade::class)->create([
            'user_id' => $user->id,
            'exchange_account_id' => $account->id,
            'exchange_id' => $exchange->id,
            'target_coin_id' => $targetCoin->id,
            'base_coin_id' => $baseCoin->id,
            'quantity' => 49.96123008,
            'price_per_unit' => 0.00002207,
            'target_percent' => 2.25,
            'gap_bought' => 0.00002174,
            'status' => 'Bought',
            'starting_shrink_differential' => 0.00000003, // market summary bid - priceperunit
        ]);

        $this->authenticatedJson('GET', "api/trades?sort=-price_bought,cpp", [], [], $user);

        dd(json_decode($this->response->content()));
    }

    public function testExitStrategyUpdate()
    {
        $exchange = Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $user = factory(User::class)->create();
        $trade = factory(Trade::class)->create([
            'user_id' => $user->id
        ]);

        $this->authenticatedJson('PATCH', "api/trades/$trade->id", [
            'current_shrink_differential' => 10,
            'target_shrink_differential' => 12
        ], [], $user);

        $response = json_decode($this->response->getContent());

        $this->authenticatedJson('PATCH', "api/trades/$trade->id", [
            'target_price' => 0.12345
        ], [], $user);

        $response = json_decode($this->response->getContent());

        dd($response);
    }
}