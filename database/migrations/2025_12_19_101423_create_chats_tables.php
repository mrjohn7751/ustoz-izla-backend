<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chats table - chat xonalari
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ustoz_id')->nullable()->constrained('ustozlar')->onDelete('cascade');
            $table->foreignId('elon_id')->nullable()->constrained('elonlar')->onDelete('set null');
            $table->enum('chat_type', ['user_ustoz', 'ustoz_admin'])->default('user_ustoz');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('user_unread_count')->default(0);
            $table->integer('ustoz_unread_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'ustoz_id']);
            $table->index('chat_type');
        });

        // Messages table - xabarlar
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('sender_type', ['user', 'ustoz', 'admin'])->default('user');
            $table->enum('message_type', ['text', 'image', 'file'])->default('text');
            $table->text('content'); // text yoki file path
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('chat_id');
            $table->index('sender_id');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats');
    }
};
