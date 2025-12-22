<?php
// database/migrations/2024_01_04_create_elonlar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elonlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ustoz_id')->constrained('ustozlar')->onDelete('cascade');
            $table->foreignId('fan_id')->constrained('fanlar')->onDelete('cascade');
            $table->string('sarlavha');
            $table->text('tavsif');
            $table->decimal('narx', 10, 2); // Oylik narx
            $table->string('joylashuv');
            $table->string('markaz_nomi')->nullable(); // O'quv markazi nomi
            $table->json('dars_kunlari'); // ["Dushanba", "Chorshanba", "Juma"]
            $table->string('dars_vaqti'); // "18:00 - 20:00"
            $table->string('rasm')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('badge')->nullable(); // "Yangi", "Top Ustoz", "Chegirma", "Tavsiya"
            $table->integer('chegirma_foiz')->nullable(); // Chegirma foizi
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->text('rad_sababi')->nullable(); // Admin rad etgan sababi
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('fan_id');
            $table->index('ustoz_id');
            $table->index('badge');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elonlar');
    }
};
