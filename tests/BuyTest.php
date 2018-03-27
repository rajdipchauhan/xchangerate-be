<?php

use App\Models\ExchangeAccount;
use App\Models\TradingBotRequest;
use App\Models\User;
use App\TradingBot\TradingBot;
use Illuminate\Support\Facades\Redis;

class BuyTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testJobInProgress()
    {
        $user = factory(User::class)->create();
        $exchange = \App\Models\Exchange::create(['id' => 'bittrex', 'name' => 'Bittrex']);
        $account = factory(ExchangeAccount::class)->create([
            'user_id' => $user->id,
            'exchange_id' => 'bittrex'
        ]);
        factory(\App\Models\Coin::class)->create([
            'id' => 'bitcoin',
            'symbol' => 'BTC'
        ]);
        factory(\App\Models\Coin::class)->create([
            'id' => 'pink',
            'symbol' => 'PINK'
        ]);
        factory(\App\Models\MarketSummary::class)->create([
            'exchange_id' => $exchange->id,
            'target_coin_id' => 'PINK',
            'base_coin_id' => 'BTC'
        ]);

        $tradingBotRequestId = null;
        Redis::shouldReceive('publish')
            ->once()
            ->withArgs(function($channel, $data) use (&$tradingBotRequestId){
                $tradingBotRequestId = array_get($data, 'trading_bot_request_id');

                return true;
            });

        $this->mockTradingBot([
            [
                'job_id' => 42,
                'job_status' => 'completed',
                'data' => [
                    'order_uuid' => 'uuid'
                ]
            ],
            [
                'job_id' => 42,
                'job_status' => 'completed',
                'data' => [
                    'order_uuid' => 'uuid',
                    'is_open' => false,
                    'quantity' => 10,
                    'quantity_remaining' => 0
                ]
            ]
        ]);

        $this->authenticatedJson('POST', '/api/buy', [
            'exchange_account_id' => $account->id,
            'base_coin_id' => 'BTC',
            'target_coin_id' => 'PINK',
            'quantity' => 0.025,
            'rate' => 0.0025
        ], [], $user);

        $response = json_decode($this->response->getContent());

        dd($response);
    }

    protected function mockTradingBot(array $expectedResponses)
    {
        $mock = \Mockery::mock(TradingBot::class . '[refreshTradingBotRequest]', [new \App\TradingBot\FakeBot()])
            ->shouldAllowMockingProtectedMethods();
        $expectation = $mock->shouldReceive('refreshTradingBotRequest');
        foreach ($expectedResponses as $expectedResponse) {
            $expectation->withArgs(function (TradingBotRequest $tradingBotRequest)
            use ($expectedResponse) {
                $tradingBotRequest->json_response = $expectedResponse;

                return $tradingBotRequest;
            });
        }
        $this->app->bind(TradingBot::class, function ($app) use ($mock) {
            return $mock;
        });
    }
}