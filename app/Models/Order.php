<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'id_transaksi',
        'nomor_resi',
        'nama_pelanggan',
        'tanggal',
        'total_harga',
        'sumber',
        'status',
        'gambar_struk',
        'ocr_raw_response',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'          => 'date',
            'total_harga'      => 'decimal:2',
            'ocr_raw_response' => 'array',
            'selesai_at'       => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function restockNotifications(): HasMany
    {
        return $this->hasMany(RestockNotification::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
            'selesai'    => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
            'dibatalkan' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
            default      => 'bg-slate-500/15 text-slate-400 border-slate-500/30',
        };
    }

    public function getSumberLabelAttribute(): string
    {
        return match ($this->sumber) {
            'scan_struk' => 'Scan Struk',
            'resi'       => 'Input Resi',
            default      => $this->sumber,
        };
    }
}
