<?php
// database/migrations/2024_01_02_create_ustozlar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ustozlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ism');
            $table->string('familiya');
            $table->string('telefon')->unique();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('joylashuv');
            $table->integer('tajriba')->default(0); // Yillar
            $table->json('fanlar')->nullable(); // Fan ID lari JSON formatda
            $table->decimal('rating', 3, 2)->default(0.00); // 0.00 - 5.00
            $table->integer('rating_count')->default(0);
            $table->integer('oquvchilar_soni')->default(0);
            $table->integer('sertifikatlar_soni')->default(0);
            $table->boolean('is_verified')->default(false); // Admin tasdiqlagan
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('sertifikatlar')->nullable(); // Sertifikat rasmlari
            $table->timestamps();

            $table->index('user_id');
            $table->index('rating');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ustozlar');
    }
};
