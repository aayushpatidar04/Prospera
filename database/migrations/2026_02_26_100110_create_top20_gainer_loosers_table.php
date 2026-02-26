<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('top20_gainer_loosers', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['gainer', 'looser'])->default('gainer');
            $table->string('sector');
            $table->string('symbol');
            $table->string('series');
            $table->string('open_price');
            $table->string('high_price');
            $table->string('low_price');
            $table->string('ltp');
            $table->string('prev_price');
            $table->string('net_price');
            $table->string('trade_quantity');
            $table->string('turnover');
            $table->string('market_type');
            $table->string('ca_ex_dt');
            $table->string('ca_purpose');
            $table->string('perChange');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top20_gainer_loosers');
    }
};
