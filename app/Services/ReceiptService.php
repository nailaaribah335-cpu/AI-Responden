<?php

namespace App\Services;

use App\Jobs\FinalizeOrderJob;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service class untuk logika bisnis Receipt Scanner.
 *
 * Menangani: validasi anti-double, pembuatan order, dispatch job finalisasi.
 */
class ReceiptService
{
    /**
     * Cek apakah data transaksi sudah pernah dimasukkan (anti double-input).
     *
     * @return string|null Pesan error jika duplikat, null jika aman.
     */
    public function checkDuplicate(int $userId, array $data): ?string
    {
        // Cek berdasarkan id_transaksi (jika ada)
        if (! empty($data['id_transaksi'])) {
            $exists = Order::where('user_id', $userId)
                ->where('id_transaksi', $data['id_transaksi'])
                ->exists();

            if ($exists) {
                return "Transaksi dengan ID '{$data['id_transaksi']}' sudah pernah dimasukkan. Input dibatalkan untuk menghindari duplikasi.";
            }
        }

        // Cek berdasarkan kombinasi nama + tanggal + total_harga
        $exists = Order::where('user_id', $userId)
            ->where('nama_pelanggan', $data['nama_pelanggan'])
            ->where('tanggal', $data['tanggal'])
            ->where('total_harga', $data['total_harga'])
            ->exists();

        if ($exists) {
            return "Data pesanan dengan nama '{$data['nama_pelanggan']}', tanggal '{$data['tanggal']}', "
                 . "dan total harga Rp " . number_format($data['total_harga'], 0, ',', '.')
                 . " sudah ada. Input dibatalkan untuk menghindari duplikasi.";
        }

        return null; // Tidak duplikat
    }

    /**
     * Buat order baru + items, lalu dispatch job finalisasi 5 menit.
     */
    public function createOrder(int $userId, array $data, array $items, string $sumber = 'scan_struk'): Order
    {
        return DB::transaction(function () use ($userId, $data, $items, $sumber) {

            // ─── 1. Simpan Order ─────────────────────────────────────
            $order = Order::create([
                'user_id'          => $userId,
                'id_transaksi'     => $data['id_transaksi'] ?? null,
                'nomor_resi'       => $data['nomor_resi'] ?? null,
                'nama_pelanggan'   => $data['nama_pelanggan'],
                'tanggal'          => $data['tanggal'],
                'total_harga'      => $data['total_harga'],
                'sumber'           => $sumber,
                'status'           => 'pending',
                'gambar_struk'     => $data['gambar_struk'] ?? null,
                'ocr_raw_response' => $data['ocr_raw_response'] ?? null,
            ]);

            // ─── 2. Simpan Items + Link ke Inventory ─────────────────
            foreach ($items as $itemData) {
                $inventory = null;

                // Coba match item ke inventory berdasarkan nama produk
                if (! empty($itemData['nama_item'])) {
                    $inventory = Inventory::where('user_id', $userId)
                        ->where('nama_produk', 'LIKE', '%' . $itemData['nama_item'] . '%')
                        ->first();
                }

                OrderItem::create([
                    'order_id'     => $order->id,
                    'inventory_id' => $inventory?->id,
                    'nama_item'    => $itemData['nama_item'],
                    'jumlah'       => $itemData['jumlah'] ?? 1,
                    'harga_satuan' => $itemData['harga_satuan'] ?? 0,
                    'subtotal'     => ($itemData['jumlah'] ?? 1) * ($itemData['harga_satuan'] ?? 0),
                ]);
            }

            // ─── 3. Dispatch Delayed Job (1 menit) ───────────────────
            FinalizeOrderJob::dispatch($order)->delay(now()->addMinutes(1));

            Log::info("[ReceiptService] Order #{$order->id} dibuat (sumber: {$sumber}). "
                     . "Finalisasi dijadwalkan pada: " . now()->addMinutes(1)->format('H:i:s'));

            return $order;
        });
    }

    /**
     * Parse response OCR mentah menjadi structured data.
     *
     * Diasumsikan OCR API mengembalikan JSON format:
     * {
     *   "nama_pelanggan": "...",
     *   "tanggal": "2026-09-29",
     *   "total_harga": 150000,
     *   "id_transaksi": "INV-001",
     *   "items": [
     *     { "nama_item": "...", "jumlah": 2, "harga_satuan": 25000 }
     *   ]
     * }
     */
    public function parseOcrResponse(array $ocrData): array
    {
        return [
            'data' => [
                'nama_pelanggan'   => $ocrData['nama_pelanggan'] ?? 'Pelanggan',
                'tanggal'          => $ocrData['tanggal'] ?? now()->toDateString(),
                'total_harga'      => (float) ($ocrData['total_harga'] ?? 0),
                'id_transaksi'     => $ocrData['id_transaksi'] ?? null,
            ],
            'items' => collect($ocrData['items'] ?? [])->map(function ($item) {
                return [
                    'nama_item'    => $item['nama_item'] ?? 'Item tidak dikenali',
                    'jumlah'       => (int) ($item['jumlah'] ?? 1),
                    'harga_satuan' => (float) ($item['harga_satuan'] ?? 0),
                ];
            })->toArray(),
        ];
    }
}
