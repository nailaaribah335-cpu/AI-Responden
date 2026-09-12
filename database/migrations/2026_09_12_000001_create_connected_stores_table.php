<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel: connected_stores
 *
 * Menyimpan konfigurasi dan token OAuth setiap toko marketplace
 * yang dihubungkan oleh Seller. Satu Seller bisa memiliki
 * beberapa toko (misal: 1 toko Shopee + 1 toko Lazada).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connected_stores', function (Blueprint $table) {
            $table->id();

            // ─── Relasi ke Seller ──────────────────────────────────────
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // ─── Identitas Platform ────────────────────────────────────
            $table->enum('platform', ['shopee', 'lazada', 'tiktok'])
                  ->comment('Nama marketplace asal');
            $table->string('shop_id')
                  ->comment('ID toko unik dari pihak marketplace');
            $table->string('shop_name')
                  ->comment('Nama toko yang ditampilkan di marketplace');

            // ─── Kredensial OAuth ──────────────────────────────────────
            // Kolom ini di-encrypt menggunakan Laravel Crypt sebelum disimpan
            $table->text('access_token')
                  ->comment('OAuth access token (encrypted)');
            $table->text('refresh_token')
                  ->nullable()
                  ->comment('OAuth refresh token (encrypted)');
            $table->timestamp('token_expires_at')
                  ->nullable()
                  ->comment('Waktu kedaluwarsa access token');

            // ─── Keamanan Webhook ──────────────────────────────────────
            $table->string('webhook_secret', 64)
                  ->nullable()
                  ->comment('Secret untuk verifikasi signature webhook dari marketplace');

            // ─── Status & Konfigurasi ──────────────────────────────────
            $table->boolean('is_active')->default(true)
                  ->comment('Aktif/nonaktif integrasi toko ini');
            $table->boolean('ai_enabled')->default(true)
                  ->comment('Master switch: aktifkan AI untuk semua chat di toko ini');
            $table->json('settings')
                  ->nullable()
                  ->comment('Pengaturan spesifik per platform (bahasa balasan, dll.)');

            $table->timestamps();

            // ─── Constraint Unik ───────────────────────────────────────
            // Satu seller tidak bisa menghubungkan toko yang sama dua kali
            $table->unique(['user_id', 'platform', 'shop_id'], 'unique_seller_platform_shop');

            // ─── Index untuk query cepat ───────────────────────────────
            $table->index(['platform', 'shop_id']);
            $table->index('token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connected_stores');
    }
};
