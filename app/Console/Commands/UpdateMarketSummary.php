<?php

namespace App\Console\Commands;

use App\Jobs\MarketSummaryJob;
use App\Models\TradingBotJob;
use App\Services\MarketOrderService;
use App\TradingBot\JobProcessor;
use App\TradingBot\Requests\SuggestionsRequest;
use App\TradingBot\TradingBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateMarketSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:market-summary';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update market summary';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Run market order every 3 seconds
        for ($i = 0; $i < 20; $i++) {
            $seconds = $i * 3;
            dispatch((new MarketSummaryJob())->delay(Carbon::now()->addSeconds($seconds)));
        }
    }
}