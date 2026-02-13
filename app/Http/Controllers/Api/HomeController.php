<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
