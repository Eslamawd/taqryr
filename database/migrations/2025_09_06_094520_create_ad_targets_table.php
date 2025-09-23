<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {Schema::create('ad_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ad_id')->constrained()->onDelete('cascade');
    $table->char('country', 2);                         // ISO 3166-1 alpha-2 code
    $table->enum('gender', ['male','female','all']);    // الجنس المستهدف
    $table->unsignedTinyInteger('age_min');             // العمر الأدنى
    $table->unsignedTinyInteger('age_max');             // العمر الأقصى
    $table->json('interests');                          // الاهتمامات (array)
    $table->json('options')->nullable();                // إضافي (مدن, لغات, أجهزة...)
    $table->timestamps();

    $table->index(['country']);
    $table->index(['gender']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('ad_targets');
    }
};
