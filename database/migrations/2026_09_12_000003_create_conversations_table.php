<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel: conversations
 *
 * Satu baris = satu "chat room" antara satu pembeli dengan toko.
 * Tabel ini adalah sumber kebenaran untuk status Human Takeover
 * dan status pemrosesan AI per percakapan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // ─── Relasi Hierarki ───────────────────────────────────────
            $table->foreignId('connected_store_id')
                  ->constrained('connected_stores')
                  ->cascadeOnDelete()
                  ->comment('Toko mana yang menerima percakapan ini');

            // ─── Identitas Percakapan dari Marketplace ─────────────────
            $table->string('platform_conversation_id')
                  ->comment('ID percakapan unik dari pihak marketplace (misal: Shopee chat_id)');
            $table->string('platform')
                  ->comment('Redundant dari connected_store, berguna untuk query langsung');

            // ─── Identitas Pembeli ─────────────────────────────────────
            $table->string('buyer_id')
                  ->comment('ID pembeli dari marketplace');
            $table->string('buyer_name')->nullable()
                  ->comment('Nama display pembeli');
            $table->string('buyer_avatar')->nullable();

            // ─── Human Takeover (Fitur Inti) ───────────────────────────
            $table->boolean('ai_enabled')->default(true)
                  ->comment('TRUE = AI aktif membalas | FALSE = Human Takeover aktif');
            $table->timestamp('ai_disabled_at')->nullable()
                  ->comment('Kapan Seller menonaktifkan AI untuk chat ini');
            $table->foreignId('taken_over_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Seller yang sedang handle secara manual');

            // ─── Status Percakapan ─────────────────────────────────────
            $table->enum('status', [
                'open',      // Percakapan aktif, menunggu balasan
                'pending',   // AI sedang memproses
                'resolved',  // Dianggap selesai
                'spam',      // Ditandai spam
            ])->default('open');

            // ─── Metadata Pesan Terakhir (Untuk Preview di Inbox) ──────
            $table->text('last_message_preview')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->unsignedSmallInteger('unread_count')->default(0);

            $table->timestamps();

            // ─── Constraint Unik ───────────────────────────────────────
            // Satu percakapan marketplace hanya ada satu baris di sini
            $table->unique(
                ['connected_store_id', 'platform_conversation_id'],
                'unique_store_conversation'
            );

            // ─── Index untuk Centralized Inbox ─────────────────────────
            $table->index(['connected_store_id', 'status', 'last_message_at']);
            $table->index('ai_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
