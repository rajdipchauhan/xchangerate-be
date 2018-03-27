<?php

namespace App\Console;

use App\Console\Commands\FixTradingBotRequestsCommand;
use App\Console\Commands\RemoveDeprecatedSuggestions;
use App\Console\Commands\UpdateLocalCoinData;
use App\Console\Commands\UpdateLocalCurrencyData;
use App\Console\Commands\UpdateMarketSummary;
use App\Console\Commands\UpdateTradesCommand;
use App\Console\Commands\WatchlistScheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        UpdateLocalCoinData::class,
        UpdateLocalCurrencyData::class,
        //UpdateSuggestions::class,
        //RemoveDeprecatedSuggestions::class,
        UpdateMarketSummary::class,
        UpdateTradesCommand::class,
        WatchlistScheduler::class,
        FixTradingBotRequestsCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
        $schedule->command(UpdateSuggestions::class)
            ->everyMinute()
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateSuggestions::class);
            });
        */

        $schedule->command(UpdateMarketSummary::class)
            ->everyMinute();

//        $schedule->command(WatchlistScheduler::class)
//            ->everyMinute();


        $schedule->command(UpdateLocalCurrencyData::class)
            ->dailyAt('10:30')
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateLocalCurrencyData::class);
            });
        $schedule->command(UpdateLocalCoinData::class)
            ->everyMinute()
            ->before(function () {
                Log::info('Running scheduled task command ' . UpdateLocalCoinData::class);
            });
        /*
        $schedule->command(RemoveDeprecatedSuggestions::class)
            ->everyTenMinutes()
            ->before(function () {
                Log::info('Running scheduled task command ' . RemoveDeprecatedSuggestions::class);
            });
        */

    }
}
