<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data demo inventaris produk UMKM.
 *
 * Jalankan: php artisan db:seed --class=InventorySeeder
 */
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user pertama (atau buat jika belum ada)
        $user = User::first();
        if (! $user) {
            $this->command->warn('Tidak ada user ditemukan. Silakan buat akun terlebih dahulu.');
            return;
        }

        $products = [
            ['sku' => 'SKU-001', 'nama_produk' => 'Produk A',          'stok' => 50,  'stok_minimum' => 5,  'harga_satuan' => 50000,  'satuan' => 'pcs'],
            ['sku' => 'SKU-002', 'nama_produk' => 'Produk B',          'stok' => 30,  'stok_minimum' => 5,  'harga_satuan' => 75000,  'satuan' => 'pcs'],
            ['sku' => 'SKU-003', 'nama_produk' => 'Produk C',          'stok' => 8,   'stok_minimum' => 10, 'harga_satuan' => 25000,  'satuan' => 'pack'],
            ['sku' => 'SKU-004', 'nama_produk' => 'Bahan Baku Premium','stok' => 3,   'stok_minimum' => 5,  'harga_satuan' => 120000, 'satuan' => 'kg'],
            ['sku' => 'SKU-005', 'nama_produk' => 'Kemasan Box Eksklusif','stok' => 100,'stok_minimum' => 15, 'harga_satuan' => 15000,  'satuan' => 'pcs'],
            ['sku' => 'SKU-006', 'nama_produk' => 'Label Stiker Brand','stok' => 200, 'stok_minimum' => 20, 'harga_satuan' => 5000,   'satuan' => 'pcs'],
            ['sku' => 'SKU-007', 'nama_produk' => 'Bubble Wrap Roll',  'stok' => 4,   'stok_minimum' => 5,  'harga_satuan' => 45000,  'satuan' => 'pack'],
            ['sku' => 'SKU-008', 'nama_produk' => 'Tali Rafia',        'stok' => 12,  'stok_minimum' => 3,  'harga_satuan' => 8000,   'satuan' => 'pack'],
        ];

        foreach ($products as $product) {
            Inventory::updateOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, ['user_id' => $user->id])
            );
        }

        $this->command->info("✅ {$this->command->getName()}: " . count($products) . " produk inventaris berhasil di-seed untuk user '{$user->name}'.");
    }
}
