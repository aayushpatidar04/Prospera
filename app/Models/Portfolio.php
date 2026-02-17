<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table =  "portfolios";

    protected $fillable = [
        'user_id',
        'stock',
        'exchange',
        'type',
        'quantity',
        'date',
        'nav_or_price',
        'total_value',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
