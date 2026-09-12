<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris = satu toko marketplace yang sudah dihubungkan.
 * Menyimpan token OAuth dan konfigurasi per toko.
 */
class ConnectedStore extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'shop_id',
        'shop_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'webhook_secret',
        'is_active',
        'ai_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'is_active'        => 'boolean',
            'ai_enabled'       => 'boolean',
            'settings'         => 'array',  // otomatis encode/decode JSON
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────────

    /** Seller pemilik toko ini */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Semua percakapan yang masuk ke toko ini */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    // ─── Helper ────────────────────────────────────────────────────

    /** Cek apakah access token sudah kedaluwarsa */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /** Label platform yang ramah untuk ditampilkan di UI */
    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'shopee'  => 'Shopee',
            'lazada'  => 'Lazada',
            'tiktok'  => 'TikTok Shop',
            default   => ucfirst($this->platform),
        };
    }
}
