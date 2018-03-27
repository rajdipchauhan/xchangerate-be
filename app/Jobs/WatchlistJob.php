<?php

namespace App\Jobs;


use App\Models\Watchlist;
use App\Services\WatchListService;

class WatchlistJob  extends Job
{
    private $watchlist;

    public function __construct(Watchlist $watchlist)
    {
        $this->watchlist = $watchlist;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WatchListService $watchListService)
    {
        $watchListService->handleWatchlistProccess($this->watchlist);
    }
}