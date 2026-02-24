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
        Schema::table('traded_stocks', function (Blueprint $table) {
            $table->unique(['identifier', 'symbol', 'series', 'marketType', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traded_stocks', function (Blueprint $table) {
            //
        });
    }
};
