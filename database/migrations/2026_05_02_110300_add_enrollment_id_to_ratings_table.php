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
        // 1. Avval user_id va ustoz_id foreign key larni olib tashlash
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['ustoz_id']);
        });

        // 2. Unique constraint ni olib tashlash
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'ustoz_id']);
        });

        // 3. Foreign key larni qaytarish
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ustoz_id')->references('id')->on('ustozlar')->onDelete('cascade');
        });

        // 4. Enrollment_id ustunini qo'shish
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignId('enrollment_id')->nullable()->after('ustoz_id')
                  ->constrained('enrollments')->onDelete('cascade');
            $table->index('enrollment_id');
        });

        // 5. Yangi unique constraint qo'shish
        Schema::table('ratings', function (Blueprint $table) {
            $table->unique(['user_id', 'enrollment_id'], 'ratings_user_enrollment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Yangi unique constraint ni olib tashlash
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique('ratings_user_enrollment_unique');
        });

        // 2. Enrollment_id ni olib tashlash
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->dropIndex(['enrollment_id']);
            $table->dropColumn('enrollment_id');
        });

        // 3. Foreign key larni olib tashlash
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['ustoz_id']);
        });

        // 4. Eski unique constraint ni qaytarish
        Schema::table('ratings', function (Blueprint $table) {
            $table->unique(['user_id', 'ustoz_id']);
        });

        // 5. Foreign key larni qaytarish
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ustoz_id')->references('id')->on('ustozlar')->onDelete('cascade');
        });
    }
};
