<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL da partial unique index qo'llab-quvvatlanmaydi
        // Unique constraint allaqachon o'chirilgan
        // Faqat oddiy index qo'shamiz

        // Oddiy index qo'shamiz (unique emas)
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['user_id', 'elon_id'], 'enrollments_user_elon_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_user_elon_index');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->unique(['user_id', 'elon_id']);
        });
    }
};
