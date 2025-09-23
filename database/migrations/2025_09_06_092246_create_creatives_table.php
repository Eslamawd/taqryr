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
        Schema::create('creatives', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ad_id')->constrained()->onDelete('cascade'); // يربط creative بالإعلان
        $table->string('name')->nullable(); // اسم اختياري
        $table->string('file_path'); // مسار التخزين المحلي أو S3
        $table->string('storage_driver')->default('local'); // local / s3 / ...
        $table->string('media_id')->nullable(); // Snapchat media ID أو أي منصة
        $table->enum('platform', ['snap', 'meta', 'google', 'tiktok']);
        $table->enum('type', ['IMAGE', 'VIDEO']);
        $table->string('platform_creative_id')->nullable(); // الـ ID بعد التسجيل في المنصة
        $table->enum('status', ['PENDING', 'UPLOADED', 'REGISTERED', 'FAILED'])->default('PENDING'); // تتبع حالة الرفع
        $table->timestamps();

        $table->index('platform');
        $table->index('platform_creative_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creatives');
    }
};
