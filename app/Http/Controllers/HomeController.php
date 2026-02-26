<?php

namespace App\Http\Controllers;

use App\Events\TradedStocks;
use App\Events\UpdateNifty50;
use App\Events\UpdateNiftybank;
use App\Events\UpdateStock;
use App\Models\Alert;
use App\Models\Blog;
use App\Models\LatestTradedStock;
use App\Models\Recommendation;
use App\Models\Top20GainerLooser;
use App\Models\TradedStock;
use App\Models\TradedStocksNAV;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Pusher\PushNotifications\PushNotifications;

class HomeController extends Controller
{

    public function dashboard(Request $request)
    {
        $search = $request->input('search');

        $query = LatestTradedStock::whereDate('timestamp', Carbon::today())
            ->orderBy('timestamp', 'desc')
            ->orderBy('id', 'asc');

        // 🔎 Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('symbol', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%")
                    ->orWhere('series', 'like', "%{$search}%");
            });
        }

        $response = Http::get('https://priceapi.moneycontrol.com/pricefeed/notapplicable/inidicesindia/in%3BNSX');
        if ($response->successful()) {
            $data = $response->json();

            $rawTimestamp = $data['data']['lastupd'] ?? now();

            $carbon = $rawTimestamp instanceof Carbon ? $rawTimestamp : Carbon::parse($rawTimestamp);

            $nifty = [
                'price' => $data['data']['pricecurrent'],
                'change' => $data['data']['CHANGE'],
                'percent' => $data['data']['pricepercentchange'],
                'lastupd' => $carbon->format('d M Y, h:i:s A'),
            ];
        }

        $response = Http::get('https://priceapi.moneycontrol.com/pricefeed/notapplicable/inidicesindia/in%3Bnbx');
        if ($response->successful()) {
            $data = $response->json();

            $rawTimestamp = $data['data']['lastupd'] ?? now();

            $carbon = $rawTimestamp instanceof Carbon ? $rawTimestamp : Carbon::parse($rawTimestamp);

            $niftybank = [
                'price' => $data['data']['pricecurrent'],
                'change' => $data['data']['CHANGE'],
                'percent' => $data['data']['pricepercentchange'],
                'lastupd' => $carbon->format('d M Y, h:i:s A'),
            ];
        }

        return Inertia::render('Dashboard', [
            'tradedStocks' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only('search'),
            'nifty' => $nifty,
            'niftybank' => $niftybank,
        ]);
    }

    public function triggerNifty50Event()
    {
        $response = Http::get('https://priceapi.moneycontrol.com/pricefeed/notapplicable/inidicesindia/in%3BNSX');
        if ($response->successful()) {
            $data = $response->json();
            event(new UpdateNifty50($data['data']));
        }
        return response()->json(['status' => 'ok']);
    }

    public function triggerNiftybankEvent()
    {
        $response = Http::get('https://priceapi.moneycontrol.com/pricefeed/notapplicable/inidicesindia/in%3Bnbx');
        if ($response->successful()) {
            $data = $response->json();
            event(new UpdateNiftybank($data['data']));
        }
        return response()->json(['status' => 'ok']);
    }

    public function triggerTradedStocksEvent(Request $request)
    {

        $search = $request->input('search');
        $page = $request->input('page', 1);

        $query = LatestTradedStock::whereDate('timestamp', Carbon::today())
            ->orderBy('timestamp', 'desc')
            ->orderBy('id', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('symbol', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%")
                    ->orWhere('series', 'like', "%{$search}%");
            });
        }

        $stocks = $query->paginate(20, ['*'], 'page', $page)->withQueryString();

        event(new TradedStocks([
            'tradedStocks' => $stocks,
            'timestamp' => $stocks->first()->timestamp
        ]));

        return response()->json(['status' => 'ok']);
    }

    public function stockView($symbol, Request $request)
    {
        $ltp = LatestTradedStock::where('symbol', $symbol)->first();

        return Inertia::render('Stocks/Details', [
            'ltp' => $ltp
        ]);
    }

    public function triggerStockEvent($symbol)
    {
        $data = LatestTradedStock::where('symbol', $symbol)->first();
        event(new UpdateStock($data));
        return response()->json(['status' => 'ok']);
    }

    public function intradayPriceMovement($symbol)
    {
        $rows = TradedStock::where('symbol', $symbol)
            ->whereDate('timestamp', today())
            ->orderBy('timestamp')
            ->get(['timestamp', 'lastPrice']);

        return response()->json($rows);
    }

    public function dailyNAVTrend($symbol)
    {
        $rows = TradedStocksNAV::where('symbol', $symbol)
            ->orderBy('date')
            ->get(['date', 'lastPrice']);

        return response()->json($rows);
    }

    public function top20()
    {
        return Inertia::render('Top20/Index', [
            'data' => Top20GainerLooser::where('category', 'gainer')->where('sector', 'NIFTY')->get()
        ]);
    }

    public function topStocks(Request $request)
    {
        $request->validate([
            'sector' => 'required',
            'category' => 'required',
        ]);

        $sector = $request->sector;

        if ($request->category == 'gainer') {
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

        return Inertia::render('Top20/Index', [
            'data' => $result
        ]);
    }

    public function _52week(Request $request){
        $type = $request->input('type', 'High');

        if($type == 'High'){
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
                'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
            ])->get('https://www.nseindia.com/api/live-analysis-data-52weekhighstock');

            if ($response->successful()) {
                $data = $response->json();
                $timestamp = $data['timestamp'];
                $count = $data['high'];
                $data = $data['data'];
            }
        }else{
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36 Edg/145.0.0.0',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-Language' => 'en-US,en;q=0.9,en-IN;q=0.8',
                'Referer' => 'https://www.nseindia.com/market-data/stocks-traded',
            ])->get('https://www.nseindia.com/api/live-analysis-data-52weeklowstock');

            if ($response->successful()) {
                $data = $response->json();
                $timestamp = $data['timestamp'];
                $count = $data['low'];
                $data = $data['data'];
            }
        }

        return Inertia::render('52week/Index', [
            'data' => $data,
            'timestamp' => $timestamp,
            'count' => $count,
            'type' => $type
        ]);
    }

    public function recommendations()
    {
        return Inertia::render('Recommendations/Index', [
            'recommendations' => Recommendation::latest()->get()
        ]);
    }

    public function storeRecommendation(Request $request)
    {
        $request->validate([
            'stock_name' => 'required|string|max:255',
            'exchange' => 'required|in:NSE,BSE',
            'recommendation_type' => 'required|in:buy,sell,hold',
            'entry_price' => 'required|numeric',
            'target_price' => 'required|numeric',
            'stop_loss' => 'required|numeric',
            'duration' => 'required|in:intraday,short-term,long-term',
            'risk_level' => 'required|in:low,medium,high',
            'analyst_notes' => 'nullable|string',
        ]);

        $recommendation = Recommendation::create($request->all());

        return redirect()->route('recommendations.index')->with('success', 'Recommendation added successfully!');
    }

    public function editRecommendation(Request $request, $id)
    {
        $request->validate([
            'stock_name' => 'required|string|max:255',
            'exchange' => 'required|in:NSE,BSE',
            'recommendation_type' => 'required|in:buy,sell,hold',
            'entry_price' => 'required|numeric',
            'target_price' => 'required|numeric',
            'stop_loss' => 'required|numeric',
            'duration' => 'required|in:intraday,short-term,long-term',
            'risk_level' => 'required|in:low,medium,high',
            'analyst_notes' => 'nullable|string',
        ]);

        $recommendation = Recommendation::findOrFail($id);
        $recommendation->update($request->all());

        return redirect()->route('recommendations.index')->with('success', 'Recommendation updated successfully!');
    }

    public function destroyRecommendation($id)
    {
        $recommendation = Recommendation::findOrFail($id);

        $recommendation->delete();

        return redirect()->route('recommendations.index')->with('success', 'Recommendation deleted successfully!');
    }

    public function sendAlert(Request $request, $id)
    {
        $recommendation = Recommendation::find($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        $data['recommendation_id'] = $id;
        // dd(config('keys.PUSHER_BEAMS_INSTANCE_ID'));
        $alert = Alert::create($data);
        // Send push notification via Pusher Beams 

        $beamsClient = new PushNotifications([
            "instanceId" => config('keys.PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey" => config('keys.PUSHER_BEAMS_SECRET_KEY'),
        ]);

        $beamsClient->publishToInterests(
            ["hello"],
            [
                "web" => [
                    "notification" => [
                        "title" => $alert->title,
                        "body" => $alert->body,
                        "icon" => "https://img.icons8.com/?size=100&id=LoL4bFzqmAa0&format=png&color=000000",
                        "deep_link" => "https://aayushpatidar04.github.io",
                        "data" => $recommendation->toArray(),
                    ]
                ]
            ]
        );

        return redirect()->route('recommendations.index')->with('success', 'Alert sent successfully!');
    }

    public function blogs()
    {
        return Inertia::render('Blogs/Index', [
            'blogs' => Blog::latest()->get(),
        ]);
    }

    public function storeBlog(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string', // HTML string 
            'image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['image'] = 'uploads/' . $filename;
        }

        $data['slug'] = Str::slug($data['title']);

        Blog::create($data);
        return redirect()->route('blogs.index')->with('success', 'Blog added successfully!');
    }

    public function editBlog(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string', // HTML string 
            'image' => 'nullable|image|max:2048',
        ]);

        $blog = Blog::findOrFail($id);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['image'] = 'uploads/' . $filename;
            if ($blog->image && file_exists(public_path($blog->image))) {
                unlink(public_path($blog->image));
            }
        } else { // Prevent overwriting with null 
            unset($data['image']);
        }
        $data['slug'] = Str::slug($data['title']);

        $blog->update($data);
        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully!');
    }

    public function destroyBlog($id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->image && file_exists(public_path($blog->image))) {
            unlink(public_path($blog->image));
        }

        $blog->delete();

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully!');
    }

    public function publishBlog(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        if ($request->status === 'publish') {
            $blog->published = true;
        } else {
            $blog->published = false;
        }
        $blog->save();

        return redirect()->route('blogs.index')->with('success', 'Blog publication status updated successfully!');
    }
}
