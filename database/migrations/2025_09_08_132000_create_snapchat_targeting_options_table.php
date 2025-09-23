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
        Schema::create('snapchat_targeting_options', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g. geos:country, demographics:languages
            $table->string('country_code', 10)->index();
            $table->json('options')->nullable();
            $table->timestamps();

            
           $table->unique(['type', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snapchat_targeting_options');
    }
};
