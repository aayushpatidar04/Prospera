<?php

namespace App\Console\Commands;

use App\Events\TopStocksUpdated;
use App\Models\Top20GainerLooser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BroadcastTopStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:top-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast top 20 gainers and losers for all sectors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sectors = ['NIFTY', 'BANKNIFTY', 'NIFTYNEXT50', 'SecGtr20', 'SecLwr20', 'FOSec', 'allSec'];
        $categories = ['gainer', 'looser'];

        foreach ($sectors as $sector) {
            foreach ($categories as $category) {
                if ($category == 'gainer') {
                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
                        'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
                    ])->get('https://www.nseindia.com/api/live-analysis-variations?index=gainers');

                    if ($response->successful()) {
                        $data = $response->json();
                        $timestamp = $data[$sector]['timestamp'];
                        $data = $data[$sector]['data'];

                        Top20GainerLooser::where('category', 'gainer')->where('sector', $sector)->delete();

                        $preparedData = collect($data)->map(function ($item) use ($sector, $timestamp) {
                            return array_merge($item, [
                                'category' => 'gainer',
                                'sector'   => $sector,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                            ]);
                        })->toArray();

                        Top20GainerLooser::insert($preparedData);
                    }

                    $result = Top20GainerLooser::where('category', 'gainer')->where('sector', $sector)->get();
                } else {
                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
                        'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
                    ])->get('https://www.nseindia.com/api/live-analysis-variations?index=loosers');

                    if ($response->successful()) {
                        $data = $response->json();
                        $timestamp = $data[$sector]['timestamp'];
                        $data = $data[$sector]['data'];

                        Top20GainerLooser::where('category', 'looser')->where('sector', $sector)->delete();

                        $preparedData = collect($data)->map(function ($item) use ($sector, $timestamp) {
                            return array_merge($item, [
                                'category' => 'looser',
                                'sector'   => $sector,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                            ]);
                        })->toArray();

                        Top20GainerLooser::insert($preparedData);
                    }

                    $result = Top20GainerLooser::where('category', 'looser')->where('sector', $sector)->get();
                }

                broadcast(new TopStocksUpdated($sector, $category, $result));

                $this->info("Broadcasted {$category} stocks for {$sector}");
            }
        }

        return Command::SUCCESS;
    }
}
