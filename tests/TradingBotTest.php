<?php

class TradingBotTest extends ApiTestCase
{
    use UsesMockHttpClientTrait;

    public function testJobInProgress()
    {
        $this->mockTradingBotHttpClient([
            [
                'expected_method' => 'GET',
                'expected_uri' => 'jobs/42',
                'expected_data' => [],
                'expected_response' => [
                    'job_id' => 42,
                    'job_status' => 'InProgress'
                ]
            ]
        ]);

        $this->authenticatedJson('GET', '/api/jobs/42');

        $response = json_decode($this->response->getContent());

        $this->assertEquals(42, object_get($response, 'data.job_id'));
        $this->assertEquals("in_progress", object_get($response, 'data.job_status'));
    }

    public function testSuggestionsJobDone()
    {
        $suggestion = [
            'coin' => 'DYN',
            'target' => 0.318703241895262,
            'exchange_trend' => 31.8703241895262,
            'market_cap' => 118935,
            'btc_impact' => 0.000023810000000000085,
            '1hr_impact' => 0.86,
            'gap' => 0.00054905,
            'cpp' => 0.000661,
            'prr' => -16.93645990922844,
            'target_score' => 0,
            'percentchange_score' => 0.25,
            'marketcap_score' => 0.25,
            'pricebtc_score' => 0.25,
            'overall_score' => 0.75
        ];
        $this->mockTradingBotHttpClient([
            [
                'expected_method' => 'GET',
                'expected_uri' => 'jobs/42',
                'expected_data' => [],
                'expected_response' => [
                    'job_id' => 42,
                    'job_status' => 'Complete',
                    'data' => [
                        $suggestion
                    ]
                ]
            ]
        ]);

        $this->authenticatedJson('GET', '/api/jobs/42');

        $response = json_decode($this->response->getContent());

        $this->assertEquals(42, object_get($response, 'job_id'));
        $this->assertEquals("completed", object_get($response, 'job_status'));

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals(42, array_get($response, 'job_id'));
        $this->assertEquals("completed", array_get($response, 'job_status'));
        foreach ($suggestion as $attribute => $value) {
            $this->assertEquals($value, array_get($response, 'data.0.' . $attribute));
        }
    }

    public function testError()
    {
        $this->mockTradingBotHttpClient([
            [
                'expected_method' => 'GET',
                'expected_uri' => 'jobs/42',
                'expected_data' => [],
                'expected_response' => [
                    'job_id' => 42,
                    'job_status' => 'Complete',
                    'err' => 'An error occurred'
                ]
            ]
        ]);

        $this->authenticatedJson('GET', '/api/jobs/42');

        $response = json_decode($this->response->getContent());

        $this->assertEquals('An error occurred', object_get($response, 'error'));
    }

    /**
     * @todo Implement buy test
     */
    public function testBuy()
    {
        $this->markTestSkipped();

        return;

        $params = [
        ];
        $this->mockTradingBotHttpClient([
            [
                'expected_method' => 'POST',
                'expected_uri' => 'getSuggestions',
                'expected_data' => $params,
                'expected_response' => [
                    'job_id' => 42,
                    'job_status' => 'DONE',
                    'data' => [
                        [

                        ]
                    ]
                ]
            ]
        ]);

        $this->authenticatedJson('POST', '/api/buy', $params);

        $response = json_decode($this->response->getContent(), true);
    }

    /**
     * @todo Implement sell test
     */
    public function testSell()
    {
        $this->markTestSkipped();

        return;

        $params = [
        ];
        $this->mockTradingBotHttpClient([
            [
                'expected_method' => 'POST',
                'expected_uri' => 'getSuggestions',
                'expected_data' => $params,
                'expected_response' => [
                    'job_id' => 42,
                    'job_status' => 'DONE',
                    'data' => [
                        [

                        ]
                    ]
                ]
            ]
        ]);

        $this->authenticatedJson('POST', '/api/sell', $params);

        $response = json_decode($this->response->getContent(), true);
    }

    /**
     * Mocks http client so communication with trading bot can be mocked
     * and only app functionality is tested.
     *
     * @param array $expectedRequests
     */
    protected function mockTradingBotHttpClient(array $expectedRequests)
    {
        $httpClient = $this->mockHttpClient($expectedRequests);
        $this->app->bind(\App\TradingBot\TradingBot::class, function ($app) use ($httpClient) {
            return new App\TradingBot\TradingBot($httpClient);
        });
    }
}