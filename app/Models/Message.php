<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris = satu pesan dalam percakapan.
 * `sender_type`: buyer | ai | seller
 * `direction`:   inbound (dari pembeli) | outbound (dari toko)
 */
class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'platform_message_id',
        'direction',
        'sender_type',
        'message_type',
        'content',
        'media_url',
        'metadata',
        'status',
        'error_message',
        'retry_count',
        'ai_context',
        'ai_tokens_used',
        'sent_at',
        'platform_created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'             => 'array',
            'ai_context'           => 'array',
            'sent_at'              => 'datetime',
            'platform_created_at'  => 'datetime',
            'retry_count'          => 'integer',
            'ai_tokens_used'       => 'integer',
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────────

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // ─── Scope ─────────────────────────────────────────────────────

    /** Hanya pesan masuk dari pembeli */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /** Hanya pesan yang gagal dikirim (untuk retry) */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // ─── Helper ────────────────────────────────────────────────────

    /** Apakah pesan ini hasil balasan AI? */
    public function isAiReply(): bool
    {
        return $this->sender_type === 'ai';
    }
}
