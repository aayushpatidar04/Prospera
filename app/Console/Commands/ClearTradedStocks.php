<?php

namespace App\Console\Commands;

use App\Models\TradedStock;
use Illuminate\Console\Command;

class ClearTradedStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traded-stocks:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all data from traded_stocks table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TradedStock::truncate();
    }
}
