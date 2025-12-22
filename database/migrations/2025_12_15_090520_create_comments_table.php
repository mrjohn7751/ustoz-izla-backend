<?php
// database/migrations/2025_12_15_090520_create_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('commentable'); // Bu avtomatik index yaratadi!
            $table->text('comment');
            $table->integer('likes_count')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            // Faqat user_id uchun index
            $table->index('user_id');
            // morphs() avtomatik yaratadi, shuning uchun qo'shimcha qo'shmaslik kerak!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
