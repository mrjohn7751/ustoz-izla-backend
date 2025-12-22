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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('ustoz_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Video yuklagan ustoz');

            $table->foreignId('fan_id')
                ->constrained('fanlar')
                ->onDelete('cascade')
                ->comment('Video fani');

            // Video ma'lumotlari
            $table->string('sarlavha')
                ->comment('Video sarlavhasi');

            $table->text('tavsif')
                ->nullable()
                ->comment('Video tavsifi/izoh');

            $table->string('video_url')
                ->comment('Video fayl yo\'li (storage/videos/)');

            $table->string('thumbnail')
                ->nullable()
                ->comment('Video muqova rasmi (storage/thumbnails/)');

            $table->integer('davomiyligi')
                ->nullable()
                ->default(0)
                ->comment('Video davomiyligi (soniyalarda)');

            // Statistika
            $table->integer('views_count')
                ->default(0)
                ->comment('Ko\'rishlar soni');

            $table->integer('likes_count')
                ->default(0)
                ->comment('Like\'lar soni');

            // Moderatsiya
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->comment('Video holati: kutilmoqda/tasdiqlangan/rad etilgan');

            $table->text('rad_sababi')
                ->nullable()
                ->comment('Rad etilish sababi (admin tomonidan)');

            // Timestamps
            $table->softDeletes()->comment('O\'chirilgan vaqt');
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index('fan_id', 'idx_fan');
            $table->index('ustoz_id', 'idx_ustoz');
            $table->index('created_at', 'idx_created');
            $table->index('views_count', 'idx_views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
