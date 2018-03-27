<?php

namespace App\TradingBot;

use App\Models\TradingBotRequest;
use App\TradingBot\Requests\AbstractTradingBotRequest;
use App\TradingBot\Requests\BuyRequest;
use App\TradingBot\Requests\CancelRequest;
use App\TradingBot\Requests\ExchangeSuggestionsRequest;
use App\TradingBot\Requests\JobRequest;
use App\TradingBot\Requests\OrderStatusRequest;
use App\TradingBot\Requests\SellRequest;
use App\TradingBot\Requests\SuggestionsRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;

/**
 * Interface for communication with automated trading bot.
 *
 * Class TradingBot
 * @package App\TradingBot
 */
class TradingBot
{
    const JOB_CREATED = 'Created';
    const JOB_IN_PROGRESS = 'InProgress';
    const JOB_COMPLETED = 'Complete';
    const SLEEP_CYCLE_SECONDS = 1;
    const WAIT = true;
    const DO_NOT_WAIT = false;

    protected $status_map = [
        self::JOB_CREATED => 'created',
        self::JOB_IN_PROGRESS => 'in_progress',
        self::JOB_COMPLETED => 'completed'
    ];

    protected $job_type_map = [
        'getSuggestions' => 'suggestion',
        'buy' => 'buy',
        'sell' => 'sell'
    ];

    /**
     * @var FakeBot
     */
    protected $fakeBot;

    public function __construct(FakeBot $fakeBot)
    {
        $this->fakeBot = $fakeBot;
    }

    /**
     * Returns JSON response content from trading bot API.
     *
     * @param string $type
     * @param AbstractTradingBotRequest $request
     * @param bool $shouldWait
     * @param bool $isTest
     * @return array
     */
    protected function request(
        $type,
        AbstractTradingBotRequest $request,
        $shouldWait = self::DO_NOT_WAIT,
        $isTest = false
    )
    {
        $this->logRequest($request);

        if ($isTest) {
            $response = $this->fakeBot->fake($request);

            return $this->parseResponse($response);
        }

        $this->validateRequest($request);
        $tradingBotRequest = TradingBotRequest::create([
            'request_type' => $type,
            'json_payload' => $request->getData(),
            'is_open' => true
        ]);
        Redis::publish(
            $this->getPublishChannel($request),
            json_encode([
                'trading_bot_request_id' => $tradingBotRequest->id,
                'request_type' => $tradingBotRequest->request_type,
                'data' => $request->getData()
            ])
        );
        $response = [
            'trading_bot_request_id' => $tradingBotRequest->id,
            'data' => []
        ];

        if ($shouldWait === self::DO_NOT_WAIT) {
            return $response;
        }

        // TODO Implement check to see if request has been read

        // Temp timeout for bot is 30 sec
        $end = Carbon::now()->addSecond(30);
        do {
            sleep(self::SLEEP_CYCLE_SECONDS);
            $tradingBotRequest = $this->refreshTradingBotRequest($tradingBotRequest);
            $requestTimedOut = $end->greaterThan(Carbon::now());
        } while (empty($tradingBotRequest->json_response) && $requestTimedOut);

        if ($requestTimedOut /* && ! $tradingBotRequest->is_read*/) {
            $tradingBotRequest->delete();
            throw new \Exception("Trading bot request timed out");
        }

        return $this->getTradingBotRequestResponse($tradingBotRequest);
    }

    public function refreshTradingBotRequest(TradingBotRequest $tradingBotRequest)
    {
        return $tradingBotRequest->fresh();
    }

    protected function logRequest(AbstractTradingBotRequest $request)
    {
        $maskFields = ['secret', 'key'];
        $data = $request->getData();
        foreach ($data as $field => $value) {
            if (in_array($field, $maskFields)) {
                $data[$field] = '*****';
            }
        }
    }

    protected function validateRequest(AbstractTradingBotRequest $request)
    {
        if (
            $request->getChannel() === $request::USER_CHANNEL &&
            ! array_get($request->getData(), 'user_id')
        ) {
            throw new InvalidArgumentException(
                "Invalid or missing user_id argument for user specific channel request"
            );
        }
    }

    protected function getPublishChannel(AbstractTradingBotRequest $request)
    {
        // TODO Revisit this if channels will be updated
        return 'my_channel';

        $channel = $request->getChannel();
        if ($channel === $request::USER_CHANNEL) {
            $channel .= '.' . array_get($request->getData(), 'user_id');
        }

        return $channel;
    }

    protected function parseResponse($response, TradingBotRequest $tradingBotRequest = null)
    {
        $jobStatus = array_get($response, 'job_status');
        $result = [
            'job_id' => array_get($response, 'job_id'),
            'job_status' => $jobStatus ? array_get($this->status_map, $jobStatus) : null,
        ];

        if ($error = array_get($response, 'err')) {
            $result['error'] = $error;
            if ($tradingBotRequest) {
                $result['response_source'] = 'bot';
                $result['request_type'] = $tradingBotRequest->request_type;
                $result['trading_bot_request_id'] = $tradingBotRequest->id;
            } else {
                $result['response_source'] = 'fake_bot';
            }
            Log::warning('Trading bot response contains an error', $result);
        }
        if ($data = array_get($response, 'data') && ! array_has($response, 'job_status')) {
            $result['data'] = $data;
        }
        if (array_get($response, 'job_type')) {
            $result['job_type'] = array_get($response, 'job_type');
        }

        $data = array_get($response, 'data', []);
        // TODO Refactor
        $result['data'] = $data;
        if (in_array($jobStatus, [self::JOB_IN_PROGRESS, self::JOB_COMPLETED]) && $data) {
            $result['data'] = $data;
        }

        return $result;
    }

    public function getTradingBotRequestResponse($tradingBotRequest)
    {
        if (! $tradingBotRequest instanceof TradingBotRequest) {
            $tradingBotRequest = TradingBotRequest::findOrFail($tradingBotRequest);
        }

        return array_merge(
            [
                'trading_bot_request_id' => $tradingBotRequest->id,
                'is_open' => $tradingBotRequest->is_open,
                'data' => []
            ],
            $this->parseResponse($tradingBotRequest->json_response, $tradingBotRequest)
        );
    }

    /**
     * @deprecated
     */
    public function getSuggestions(SuggestionsRequest $request)
    {
        return $this->request('getSuggestions', $request);
    }

    /**
     * @deprecated
     */
    public function getExchangeSuggestions(ExchangeSuggestionsRequest $request)
    {
        return $this->request("exchangeSuggestions", $request);
    }

    public function buy(BuyRequest $request, $shouldWait = false, $isTest = false)
    {
        return $this->request('buy', $request, $shouldWait, $isTest);
    }

    public function sell(SellRequest $request, $shouldWait)
    {
        return $this->request('sell', $request, $shouldWait);
    }

    public function getJob(JobRequest $request)
    {
        return $this->request('job', $request);
    }

    public function cancel(CancelRequest $request, $shouldWait = false, $isTest = false)
    {
        return $this->request('cancel', $request, $shouldWait, $isTest);
    }
}