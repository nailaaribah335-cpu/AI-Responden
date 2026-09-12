<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel: messages
 *
 * Setiap baris = satu pesan tunggal dalam sebuah percakapan.
 * Mencakup pesan dari pembeli (inbound), balasan AI (ai),
 * dan balasan manual Seller (seller).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // ─── Relasi ────────────────────────────────────────────────
            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            // ─── Identitas Pesan dari Marketplace ─────────────────────
            $table->string('platform_message_id')
                  ->nullable()
                  ->comment('ID pesan dari marketplace. NULL jika dibuat oleh sistem.');

            // ─── Arah & Pengirim ───────────────────────────────────────
            $table->enum('direction', ['inbound', 'outbound'])
                  ->comment('inbound = dari pembeli | outbound = dari toko');
            $table->enum('sender_type', ['buyer', 'ai', 'seller'])
                  ->comment('buyer=pembeli, ai=dibalas AI, seller=dibalas manual');

            // ─── Konten Pesan ──────────────────────────────────────────
            $table->enum('message_type', [
                'text',
                'image',
                'video',
                'file',
                'product_card', // Kiriman kartu produk dari marketplace
                'order_card',   // Kiriman kartu order dari marketplace
                'sticker',
            ])->default('text');
            $table->text('content')
                  ->nullable()
                  ->comment('Isi teks pesan. Nullable untuk tipe non-teks.');
            $table->string('media_url')
                  ->nullable()
                  ->comment('URL media (gambar, video, file) dari marketplace CDN');
            $table->json('metadata')
                  ->nullable()
                  ->comment('Data tambahan spesifik platform: product_id, order_id, sticker_id, dll.');

            // ─── Status Pengiriman ─────────────────────────────────────
            $table->enum('status', [
                'queued',    // Masuk antrian Job Laravel
                'sending',   // Sedang dikirim ke API marketplace
                'sent',      // Berhasil terkirim ke marketplace
                'delivered', // Dikonfirmasi diterima pembeli (jika platform support)
                'failed',    // Gagal terkirim
            ])->default('queued');
            $table->text('error_message')
                  ->nullable()
                  ->comment('Pesan error jika status = failed');
            $table->unsignedTinyInteger('retry_count')
                  ->default(0)
                  ->comment('Jumlah percobaan ulang pengiriman');

            // ─── AI Processing Metadata ────────────────────────────────
            $table->json('ai_context')
                  ->nullable()
                  ->comment('System prompt + token count yang digunakan saat AI merespons');
            $table->unsignedSmallInteger('ai_tokens_used')
                  ->nullable()
                  ->comment('Token yang dikonsumsi untuk logging biaya AI');

            // ─── Waktu ─────────────────────────────────────────────────
            $table->timestamp('sent_at')
                  ->nullable()
                  ->comment('Waktu pesan berhasil terkirim ke marketplace');
            $table->timestamp('platform_created_at')
                  ->nullable()
                  ->comment('Timestamp asli pesan dari marketplace (bisa berbeda dengan created_at)');

            $table->timestamps(); // created_at = pesan masuk ke sistem kita

            // ─── Index untuk performa ──────────────────────────────────
            $table->index(['conversation_id', 'direction', 'created_at']);
            $table->index('platform_message_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
