<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Blog;
use App\Models\LatestTradedStock;
use App\Models\Portfolio;
use App\Models\Recommendation;
use App\Models\Top20GainerLooser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    protected array $middleware = [
        'auth:sanctum',
        'role:User',
    ];

    public function recommendations(Request $request)
    {
        $recommendations = Recommendation::whereDate('created_at', Carbon::today())->get();

        return response()->json([
            'status' => 'success',
            'count' => $recommendations->count(),
            'data' => $recommendations,
        ]);
    }

    public function alerts(Request $request)
    {
        $alerts = Alert::with(['recommendation'])->whereDate('created_at', Carbon::today())->get();

        return response()->json([
            'status' => 'success',
            'count' => $alerts->count(),
            'data' => $alerts,
        ]);
    }

    public function blogs(Request $request)
    {
        $blogs = Blog::where('published', 1)->latest()->get();

        return response()->json([
            'status' => 'success',
            'count' => $blogs->count(),
            'data' => $blogs,
        ]);
    }

    public function portfolios(Request $request)
    {
        $user = $request->user();
        $portfolios = $user->portfolios()->with('latestTradedStock')->latest()->get();

        return response()->json([
            'status' => 'success',
            'count' => $portfolios->count(),
            'data' => $portfolios,
        ]);
    }

    public function storePortfolio(Request $request)
    {
        try {
            $data = $request->validate([
                'stock' => 'required|string|exists:latest_traded_stocks,symbol',
                'exchange' => 'required|in:NSE,BSE',
                'type' => 'required|in:Buy,Sell',
                'quantity' => 'required|integer|min:1',
                'date' => 'required|date',
                'nav_or_price' => 'required|numeric|min:0',
            ]);
            $data['user_id'] = Auth::id();
            $data['total_value'] = $data['quantity'] * $data['nav_or_price'];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 200);
        }

        $portfolio = Portfolio::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $portfolio,
            'message' => 'Portfolio entry created successfully.',
        ], 200);
    }

    public function updatePortfolio(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'stock' => 'required|string|max:255',
                'exchange' => 'required|in:NSE,BSE',
                'type' => 'required|in:Buy,Sell',
                'quantity' => 'required|integer|min:1',
                'date' => 'required|date',
                'nav_or_price' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 200);
        }

        $portfolio = Portfolio::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$portfolio) {
            return response()->json(['status' => 'error', 'message' => 'Portfolio entry not found.'], 200);
        }
        $data['total_value'] = $data['quantity'] * $data['nav_or_price'];
        $portfolio->update($data);
        return response()->json([
            'status' => 'success',
            'data' => $portfolio,
            'message' => 'Portfolio entry updated successfully.',
        ], 200);
    }

    public function deletePortfolio(Request $request, $id)
    {
        $portfolio = Portfolio::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$portfolio) {
            return response()->json(['status' => 'error', 'message' => 'Portfolio entry not found.'], 200);
        }
        $portfolio->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Portfolio entry deleted successfully.',
        ], 200);
    }

    public function stocks()
    {
        $stocks = LatestTradedStock::select(['identifier', 'symbol'])->distinct('symbol')->orderBy('id')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'stocks fetched successfully!',
            'data' => $stocks
        ]);
    }

    public function tradedStocks()
    {
        $stocks = LatestTradedStock::whereDate('timestamp', Carbon::today())
            ->orderBy('timestamp', 'desc')
            ->orderBy('id', 'asc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $stocks,
        ]);
    }

    public function sectors()
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
            'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
        ])->get('https://www.nseindia.com/api/live-analysis-variations?index=gainers');

        if ($response->successful()) {
            $data = $response->json();
            $legends = $data['legends'];

            return response()->json([
                'status' => 'success',
                'sectors' => $legends
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'something went wrong, please try again later!',
        ]);
    }

    public function top20Gainers($sector)
    {
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

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function top20Loosers($sector)
    {
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

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
