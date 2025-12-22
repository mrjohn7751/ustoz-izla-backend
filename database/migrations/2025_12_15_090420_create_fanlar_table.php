<?php
// database/migrations/2024_01_03_create_fanlar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fanlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi')->unique(); // Matematika, Ingliz tili, va boshqalar
            $table->string('kod')->unique(); // MAT, ENG, FIZ, KIM
            $table->string('rasm')->nullable(); // Fan rasmi
            $table->text('tavsif')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Tartib raqami
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fanlar');
    }
};
