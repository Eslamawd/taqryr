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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // صاحب الإعلان
            $table->enum('platform', ['snap', 'meta', 'google', 'tiktok']);
            $table->string('name');
            $table->string('objective')->nullable();     // هدف الإعلان
            $table->decimal('budget', 12, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'paused', 'sent'])->default('pending');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();     // الاستهداف (JSON)
            $table->string('platform_ad_id')->nullable(); // ID من المنصة بعد الإرسال
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
