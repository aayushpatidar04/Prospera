<?php

namespace App\Console\Commands;

use App\Events\TradedStocks;
use App\Models\TradedStock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTradedStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:traded-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch traded stocks data from NSE API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        sleep(5);
        for ($i = 0; $i < 3; $i++) {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
                'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
            ])->get('https://www.nseindia.com/api/live-analysis-stocksTraded');

            if ($response->successful()) {
                $data = $response->json();

                $rows = collect($data['total']['data'])->map(function ($item) use ($data) {
                    return [
                        'identifier' => $item['identifier'],
                        'symbol' => $item['symbol'],
                        'series' => $item['series'],
                        'marketType' => $item['marketType'],
                        'timestamp' => Carbon::parse($data['timestamp']),
                        'pchange' => $item['pchange'],
                        'change' => $item['change'],
                        'previousClose' => $item['previousClose'],
                        'lastPrice' => $item['lastPrice'],
                        'totalTradedVolume' => $item['totalTradedVolume'],
                        'issuedCap' => $item['issuedCap'],
                        'totalTradedValue' => $item['totalTradedValue'],
                        'totalMarketCap' => $item['totalMarketCap'],
                    ];
                })->toArray();

                foreach (array_chunk($rows, 500) as $chunk) {
                    TradedStock::upsert(
                        $chunk,
                        ['identifier', 'symbol', 'series', 'marketType', 'timestamp'], // unique keys
                        ['pchange', 'change', 'previousClose', 'lastPrice', 'totalTradedVolume', 'issuedCap', 'totalTradedValue', 'totalMarketCap'] // fields to update
                    );
                }
            }
            if ($i < 2) {
                sleep(15);
            }
        }
    }
}
