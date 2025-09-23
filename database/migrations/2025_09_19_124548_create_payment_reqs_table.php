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
        Schema::create('payment_reqs', function (Blueprint $table) {
        $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // علاقة مع جدول users
            $table->decimal('amount', 10, 2); // المبلغ
            $table->string('image')->nullable(); // صورة التحويل
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة الطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reqs');
    }
};
