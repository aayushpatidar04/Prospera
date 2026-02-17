<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = "alerts";

    protected $fillable = [
        'recommendation_id',
        'title',
        'body',
    ];

    public function recommendation()
    {
        return $this->belongsTo(Recommendation::class);
    }
}
