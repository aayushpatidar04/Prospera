<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradedStocksNAV extends Model
{
    protected $table = "traded_stocks_n_a_v_s";

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
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
