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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('elon_id')->constrained('elonlar')->onDelete('cascade');
            $table->foreignId('ustoz_id')->constrained('ustozlar')->onDelete('cascade');

            // Kurs ma'lumotlari
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Baholash ma'lumotlari
            $table->boolean('can_rate')->default(false); // 1 oydan keyin true bo'ladi
            $table->timestamp('can_rate_from')->nullable(); // Qachondan baholash mumkin
            $table->boolean('has_rated')->default(false); // Baholaganmi
            $table->timestamp('rated_at')->nullable(); // Qachon baholagan

            // Qo'shimcha ma'lumotlar
            $table->text('notes')->nullable(); // O'quvchi izohi
            $table->decimal('paid_amount', 10, 2)->nullable(); // To'langan summa
            $table->string('payment_status')->default('pending'); // pending, paid, partial

            $table->timestamps();
            $table->softDeletes();

            // Bir o'quvchi bir e'longa faqat bir marta yozilishi mumkin
            $table->unique(['user_id', 'elon_id']);

            // Indexlar
            $table->index('user_id');
            $table->index('elon_id');
            $table->index('ustoz_id');
            $table->index('status');
            $table->index('can_rate');
            $table->index('has_rated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
