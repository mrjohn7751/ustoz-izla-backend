<?php
// database/migrations/2024_01_08_create_ratings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ustoz_id')->constrained('ustozlar')->onDelete('cascade');
            $table->integer('rating'); // 1-5 yulduz
            $table->text('comment')->nullable();
            $table->timestamps();

            // Bir user bir ustozni faqat bir marta baholay oladi
            $table->unique(['user_id', 'ustoz_id']);

            $table->index('ustoz_id');
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
