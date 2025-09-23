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
        Schema::create('tik_tok_tokens', function (Blueprint $table) {
            $table->id();
            $table->longText('access_token');
            $table->longText('refresh_token')->nullable();  // الـ Refresh Token
            $table->timestamp('expires_at');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tik_tok_tokens');
    }
};
