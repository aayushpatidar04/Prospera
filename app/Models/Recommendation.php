<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $table = 'recommendations';

    protected $fillable = [
        'stock_name',
        'exchange',
        'recommendation_type',
        'entry_price',
        'target_price',
        'stop_loss',
        'duration',
        'risk_level',
        'analyst_notes',
    ];

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
