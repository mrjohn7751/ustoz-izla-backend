<?php
// database/migrations/2025_12_15_090521_create_favorites_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('favoritable'); // Bu avtomatik index yaratadi!
            $table->timestamps();

            // Unique constraint
            $table->unique(['user_id', 'favoritable_type', 'favoritable_id']);

            // Faqat user_id uchun index
            $table->index('user_id');
            // morphs() avtomatik yaratadi, shuning uchun qo'shimcha qo'shmaslik kerak!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
