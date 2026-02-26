<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Top20GainerLooser extends Model
{
    protected $table = "top20_gainer_loosers";

    protected $fillable = [
        'category',
        'sector',
        'symbol',
        'series',
        'open_price',
        'high_price',
        'low_price',
        'ltp',
        'prev_price',
        'net_price',
        'trade_quantity',
        'turnover',
        'market_type',
        'ca_ex_dt',
        'ca_purpose',
        'perChange',
    ];
}
