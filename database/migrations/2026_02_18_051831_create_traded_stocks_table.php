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
        Schema::create('traded_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('symbol');
            $table->string('series');
            $table->string('marketType');
            $table->decimal('pchange', 8, 2);
            $table->decimal('change', 12, 2);
            $table->decimal('previousClose', 12, 2);
            $table->decimal('lastPrice', 12, 2);
            $table->decimal('totalTradedVolume', 15, 4);
            $table->decimal('issuedCap', 20, 0);
            $table->decimal('totalTradedValue', 20, 4);
            $table->decimal('totalMarketCap', 20, 4);
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traded_stocks');
    }
};
