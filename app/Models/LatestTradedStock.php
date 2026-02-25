<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatestTradedStock extends Model
{
    protected $table = "latest_traded_stocks";

    protected $fillable = [
        'identifier',
        'symbol',
        'series',
        'marketType',
        'pchange',
        'change',
        'previousClose',
        'lastPrice',
        'totalTradedVolume',
        'issuedCap',
        'totalTradedValue',
        'totalMarketCap',
        'timestamp',
    ];
}
