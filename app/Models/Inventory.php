<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'user_id',
        'sku',
        'nama_produk',
        'stok',
        'stok_minimum',
        'harga_satuan',
        'satuan',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function restockNotifications(): HasMany
    {
        return $this->hasMany(RestockNotification::class);
    }

    // ─── Helper ──────────────────────────────────────────────────

    /**
     * Cek apakah stok di bawah atau sama dengan batas minimum.
     */
    public function needsRestock(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Kurangi stok, tidak boleh di bawah 0.
     */
    public function kurangiStok(int $jumlah): void
    {
        $this->stok = max(0, $this->stok - $jumlah);
        $this->save();
    }
}
