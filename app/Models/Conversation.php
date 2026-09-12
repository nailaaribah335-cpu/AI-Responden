<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris = satu chat room antara pembeli dengan toko.
 * Kolom `ai_enabled` adalah toggle untuk fitur Human Takeover.
 */
class Conversation extends Model
{
    protected $fillable = [
        'connected_store_id',
        'platform_conversation_id',
        'platform',
        'buyer_id',
        'buyer_name',
        'buyer_avatar',
        'ai_enabled',
        'ai_disabled_at',
        'taken_over_by',
        'status',
        'last_message_preview',
        'last_message_at',
        'unread_count',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled'      => 'boolean',
            'ai_disabled_at'  => 'datetime',
            'last_message_at' => 'datetime',
            'unread_count'    => 'integer',
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────────

    public function store()
    {
        return $this->belongsTo(ConnectedStore::class, 'connected_store_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function takenOverBy()
    {
        return $this->belongsTo(User::class, 'taken_over_by');
    }

    // ─── Scope ─────────────────────────────────────────────────────

    /** Hanya percakapan yang aktif (belum resolved/spam) */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // ─── Helper ────────────────────────────────────────────────────

    /**
     * Toggle Human Takeover.
     * Jika AI di-OFF, Seller bisa membalas manual.
     */
    public function toggleAi(User $seller): void
    {
        $this->update([
            'ai_enabled'     => ! $this->ai_enabled,
            'ai_disabled_at' => $this->ai_enabled ? now() : null,
            'taken_over_by'  => $this->ai_enabled ? $seller->id : null,
        ]);
    }
}
