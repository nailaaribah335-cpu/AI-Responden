<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel-tabel untuk sistem Smart Receipt Scanner & Auto-Entry.
     *
     * - orders            : Pesanan utama (dari scan struk / resi)
     * - order_items       : Detail item per pesanan
     * - inventories       : Stok produk UMKM
     * - restock_notifications : Log pengingat restock
     */
    public function up(): void
    {
        // ─── Tabel Inventaris / Stok Produk ──────────────────────────
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique()->comment('Kode SKU unik produk');
            $table->string('nama_produk');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(5)->comment('Batas restock alert');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->string('satuan')->default('pcs');
            $table->timestamps();

            $table->index(['user_id', 'nama_produk']);
        });

        // ─── Tabel Pesanan Utama ─────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('id_transaksi')->nullable()->comment('ID unik transaksi / nomor invoice');
            $table->string('nomor_resi')->nullable()->index();
            $table->string('nama_pelanggan');
            $table->date('tanggal');
            $table->decimal('total_harga', 15, 2);
            $table->enum('sumber', ['scan_struk', 'resi'])->default('scan_struk');
            $table->enum('status', ['pending', 'selesai', 'dibatalkan'])->default('pending');
            $table->string('gambar_struk')->nullable()->comment('Path file gambar struk');
            $table->json('ocr_raw_response')->nullable()->comment('Response mentah dari OCR API');
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();

            // Anti double-input: Unique constraint kombinasi
            $table->unique(['user_id', 'nama_pelanggan', 'tanggal', 'total_harga'], 'orders_anti_double_unique');
            $table->unique(['user_id', 'id_transaksi'], 'orders_id_transaksi_unique');
        });

        // ─── Tabel Detail Item Pesanan ───────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            $table->string('nama_item');
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // ─── Tabel Log Notifikasi Restock ────────────────────────────
        Schema::create('restock_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('stok_tersisa');
            $table->string('channel')->default('log')->comment('log, mail, dsb.');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_notifications');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('inventories');
    }
};
