<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradedStock extends Model
{
    protected $table = 'traded_stocks';

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
