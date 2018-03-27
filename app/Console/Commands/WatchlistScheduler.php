<?php

namespace App\Console\Commands;

use App\Jobs\WatchlistJob;
use App\Models\Watchlist;
use App\TradingBot\JobProcessor;
use Illuminate\Console\Command;

class WatchlistScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watchlist:check';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process watchlist executions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        //TODO: create logic to check if we need to run one of the methods
        $watchlist = Watchlist::whereNotNull('interval')->where(function($query){
            $query->where('sms', true)->orWhere('email', true)->orWhere('execute', true);
        })->get();
        foreach ($watchlist as $item) {

            //TODO: check is sms/email/execute enabled
            dispatch(new WatchlistJob($item));
        }
    }
}