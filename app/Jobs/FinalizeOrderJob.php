<?php

namespace App\Jobs;

use App\Mail\RestockAlertMail;
use App\Models\Order;
use App\Models\RestockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job: Finalisasi Pesanan Otomatis setelah 5 menit.
 *
 * Dipanggil dengan: FinalizeOrderJob::dispatch($order)->delay(now()->addMinutes(5));
 *
 * Alur:
 * 1. Cek apakah order masih 'pending' (belum dibatalkan manual).
 * 2. Ubah status menjadi 'selesai', catat waktu selesai.
 * 3. Potong stok inventaris untuk setiap item.
 * 4. Cek stok rendah → trigger notifikasi restock (Log + Mail).
 */
class FinalizeOrderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(): void
    {
        // Guard: Jika status bukan 'pending', skip.
        $this->order->refresh();
        if (! $this->order->isPending()) {
            Log::info("[FinalizeOrderJob] Order #{$this->order->id} sudah berstatus '{$this->order->status}', skip.");
            return;
        }

        DB::transaction(function () {
            // ─── 1. Update status ke 'selesai' ──────────────────────
            $this->order->update([
                'status'     => 'selesai',
                'selesai_at' => now(),
            ]);

            Log::info("[FinalizeOrderJob] Order #{$this->order->id} → status 'selesai'.");

            // ─── 2. Auto-potong stok ─────────────────────────────────
            $this->order->load('items.inventory');

            foreach ($this->order->items as $item) {
                if (! $item->inventory) {
                    Log::warning("[FinalizeOrderJob] Item '{$item->nama_item}' tidak terhubung ke inventaris, skip potong stok.");
                    continue;
                }

                $inventory = $item->inventory;
                $stokSebelum = $inventory->stok;
                $inventory->kurangiStok($item->jumlah);

                Log::info("[FinalizeOrderJob] Stok '{$inventory->nama_produk}': {$stokSebelum} → {$inventory->stok} (-{$item->jumlah}).");

                // ─── 3. Cek restock alert (stok ≤ batas minimum) ─────
                if ($inventory->needsRestock()) {
                    $this->triggerRestockAlert($inventory, $item->jumlah);
                }
            }
        });
    }

    /**
     * Trigger notifikasi restock: simpan ke DB + kirim log/mail.
     */
    private function triggerRestockAlert($inventory, int $jumlahDikurangi): void
    {
        // Simpan ke tabel restock_notifications
        $notification = RestockNotification::create([
            'user_id'       => $this->order->user_id,
            'inventory_id'  => $inventory->id,
            'order_id'      => $this->order->id,
            'stok_tersisa'  => $inventory->stok,
            'channel'       => config('mail.mailer') === 'log' ? 'log' : 'mail',
        ]);

        // Log peringatan
        Log::warning("[RESTOCK ALERT] ⚠️ Produk '{$inventory->nama_produk}' (SKU: {$inventory->sku}) "
            . "stok rendah: {$inventory->stok} {$inventory->satuan} tersisa. "
            . "Batas minimum: {$inventory->stok_minimum}. Segera restock!");

        // Kirim email (jika MAIL_MAILER bukan 'log', kirim ke Mailtrap/SMTP)
        try {
            $user = $this->order->user;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new RestockAlertMail($inventory, $this->order));
            }
        } catch (\Throwable $e) {
            Log::error("[RESTOCK ALERT] Gagal kirim email: " . $e->getMessage());
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[FinalizeOrderJob] GAGAL untuk Order #{$this->order->id}: " . $exception->getMessage());
    }
}
