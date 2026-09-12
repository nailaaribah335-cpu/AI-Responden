<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel: knowledge_bases
 *
 * Berisi "otak" AI — semua konten yang akan di-inject ke dalam
 * System Prompt ketika AI menjawab chat dari pembeli.
 *
 * Setiap entri bertipe (FAQ, shipping_rule, product_ref, dll.)
 * dan bisa di-scope ke satu toko spesifik atau berlaku untuk
 * semua toko milik Seller tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();

            // ─── Relasi ke Seller ──────────────────────────────────────
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Opsional: scope ke toko tertentu. NULL = berlaku untuk semua toko.
            $table->foreignId('connected_store_id')
                  ->nullable()
                  ->constrained('connected_stores')
                  ->nullOnDelete()
                  ->comment('NULL = berlaku untuk semua toko Seller ini');

            // ─── Kategorisasi Konten ───────────────────────────────────
            $table->enum('type', [
                'faq',           // Tanya-jawab umum (Q&A)
                'shipping_rule', // Aturan dan info pengiriman
                'product_ref',   // Referensi produk, harga, stok
                'promo',         // Info promo / diskon yang sedang berjalan
                'policy',        // Kebijakan toko (retur, garansi, dll.)
                'custom',        // Konten bebas lainnya
            ])->default('faq');

            // ─── Konten Inti ───────────────────────────────────────────
            $table->string('title')
                  ->comment('Judul / topik (misal: "Aturan Pengiriman Luar Jawa")');
            $table->text('content')
                  ->comment('Isi konten yang akan diinjeksikan ke system prompt AI');

            // ─── Metadata untuk Retrieval ──────────────────────────────
            $table->json('keywords')
                  ->nullable()
                  ->comment('Tag kata kunci untuk relevansi matching (array of strings)');
            $table->unsignedTinyInteger('priority')
                  ->default(5)
                  ->comment('1 (tertinggi) – 10 (terendah): urutan injeksi ke prompt');

            // ─── Status ────────────────────────────────────────────────
            $table->boolean('is_active')->default(true)
                  ->comment('Nonaktifkan entri tanpa menghapusnya');

            $table->timestamps();
            $table->softDeletes(); // Soft delete untuk mencegah kehilangan data

            // ─── Index ─────────────────────────────────────────────────
            $table->index(['user_id', 'type', 'is_active']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
