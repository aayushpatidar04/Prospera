<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Blog;
use App\Models\Portfolio;
use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $portfolios = $user->portfolios()->latest()->get();

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
                'stock' => 'required|string|max:255',
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
}
