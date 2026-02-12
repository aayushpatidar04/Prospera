<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function recommendations(){
        return Inertia::render('Recommendations/Index', [ 
            'recommendations' => Recommendation::all() 
        ]);
    }

    public function storeRecommendation(Request $request){
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

    public function editRecommendation(Request $request, $id){
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

    public function destroyRecommendation($id){
        $recommendation = Recommendation::findOrFail($id); 
        
        $recommendation->delete(); 
        
        return redirect()->route('recommendations.index')->with('success', 'Recommendation deleted successfully!');
    }
}
