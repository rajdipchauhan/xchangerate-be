<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class UpdateLocalCurrencyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:currency';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update local currency exchange data';
    /**
     * @var CurrencyService
     */
    protected $currencyService;

    /**
     * Create a new command instance.
     *
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {
        parent::__construct();

        $this->currencyService = $currencyService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->currencyService->updateLocalCurrencyData();
    }
}