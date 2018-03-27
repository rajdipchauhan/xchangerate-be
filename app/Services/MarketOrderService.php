<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CurrencyRate;
use App\Models\Exchange;
use App\Models\MarketSummary;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MarketOrderService
{
    /**
     * @var Client
     */
    protected $bittrexHttpClient;
    /**
     * @var Client
     */
    protected $bitfinexHttpClient;
    /**
     * @var Log
     */
    protected $log;

    public function __construct(Client $bitfinexHttpClient, Client $bittrexHttpClient, Log $log)
    {
        $this->bittrexHttpClient = $bittrexHttpClient;
        $this->bitfinexHttpClient = $bitfinexHttpClient;
        $this->log = $log;
    }

    /**
     * Get Market order value for the coin depending on the exchange
     *
     * @param $exchange
     * @param $coin
     * @return Collection|null|static
     */
    public function retrieveMarketOrder($exchange, $coin, $sell = false)
    {
        $data = null;
        if ($exchange == 'bittrex') {
            $url = config('services.marketorder.bittrex_url');
            $url = str_replace('{COIN}', $coin, $url);
            if ($sell)
                $url = str_replace('{TYPE}', "buy", $url);
            else
                $url = str_replace('{TYPE}', "sell", $url);


            $response = $this->bittrexHttpClient->request('GET', $url);
            $body = json_decode($response->getBody()->getContents(), true);
            if (isset($body['result']) && $body['result']) {
                $data = new Collection($body['result']);
                if ($data) {
                    $data = $data->map(function ($record) {
                        $record['price'] = $record['Rate'];
                        $record['amount'] = $record['Quantity'];
                        unset($record['Quantity']);
                        unset($record['Rate']);
                        return $record;
                    });
                }
            }
        }
        if ($exchange == 'bitfinex') {
            $url = config('services.marketorder.bitfinex_url');
            $url = str_replace('{COIN}', $coin, $url);
            if ($sell)
                $url = str_replace('{TYPE}', "limit_bids=0", $url);
            else
                $url = str_replace('{TYPE}', "limit_asks=0", $url);

            $response = $this->bitfinexHttpClient->request('GET', $url);
            $body = json_decode($response->getBody()->getContents(), true);
            $key = 'bids';
            if($sell)
                $key = 'asks';
            if (isset($body[$key]) && $body[$key]) {
                $data = new Collection($body[$key]);
                if ($data) {
                    $data = $data->map(function ($record) {
                        unset($record['timestamp']);
                        return $record;
                    });
                }
            }

        }

        return $data;
    }


    public function updateMarketSummary()
    {

        $exchanges = Exchange::get();
        foreach ($exchanges as $exchange) {
            if ($exchange->id == 'bittrex') {
                $url = config('services.marketorder.bittrex_summary_url');
                $response = $this->bittrexHttpClient->request('GET', $url);
                $body = json_decode($response->getBody()->getContents(), true);

                if (isset($body['result']) && $body['result']) {
                    $data = new Collection($body['result']);
                    $insertData = [];
                    foreach ($data as $record) {
                        $insert = [];
                        $insert['exchange_id'] = $exchange->id;
                        $insert['market_name'] = isset($record['MarketName']) ? $record['MarketName'] : '';
                        if ($insert['market_name']) {
                            $marketName = explode('-', $insert['market_name']);
                            if (count($marketName) == 2) {
                                $insert['base_coin_id'] = $marketName[0];
                                $insert['target_coin_id'] = $marketName[1];
                            }
                        }
                        $insert['high'] = isset($record['High']) ? $record['High'] : 0;
                        $insert['low'] = isset($record['Low']) ? $record['Low'] : 0;
                        $insert['volume'] = isset($record['Volume']) ? $record['Volume'] : 0;
                        $insert['last'] = isset($record['Last']) ? $record['Last'] : 0;
                        $insert['base_volume'] = isset($record['BaseVolume']) ? $record['BaseVolume'] : 0;
                        $insert['time_stamp'] = isset($record['TimeStamp']) ? $record['TimeStamp'] : '';
                        $insert['bid'] = isset($record['Bid']) ? $record['Bid'] : 0;
                        $insert['ask'] = isset($record['Ask']) ? $record['Ask'] : 0;
                        $insert['open_buy_orders'] = isset($record['OpenBuyOrders']) ? $record['OpenBuyOrders'] : 0;
                        $insert['open_sell_orders'] = isset($record['OpenSellOrders']) ? $record['OpenSellOrders'] : 0;
                        $insert['prev_day'] = isset($record['PrevDay']) ? $record['PrevDay'] : 0;
                        $insert['created'] = isset($record['Created']) ? $record['Created'] : '';
                        $insertData[] = $insert;
                    }

                    MarketSummary::whereExchangeId($exchange->id)->delete();
                    MarketSummary::insert($insertData);

                }
            }
        }
    }

}