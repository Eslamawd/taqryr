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
        Schema::create('ad_stats', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('ad_id')->constrained()->onDelete('cascade');
            $table->date('stat_date'); 
            $table->string('granularity')->default('TOTAL'); // TOTAL, DAY, HOUR
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('swipes')->default(0); // clicks
            $table->decimal('spend', 12, 2)->default(0.00);
            $table->json('raw')->nullable(); // store raw API chunk for debugging
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_stats');
    }
};
