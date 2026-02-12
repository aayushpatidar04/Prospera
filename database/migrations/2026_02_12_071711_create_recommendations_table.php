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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('stock_name'); 
            $table->enum('exchange', ['NSE', 'BSE'])->nullable(); 
            $table->enum('recommendation_type', ['buy', 'sell', 'hold']); 
            $table->decimal('entry_price', 10, 2)->nullable(); 
            $table->decimal('target_price', 10, 2)->nullable(); 
            $table->decimal('stop_loss', 10, 2)->nullable(); 
            $table->string('duration')->nullable(); // e.g. short-term, long-term 
            $table->string('risk_level')->nullable(); // low, medium, high 
            $table->text('analyst_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
