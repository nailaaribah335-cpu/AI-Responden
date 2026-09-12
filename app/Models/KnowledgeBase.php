<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * "Otak AI" — konten yang di-inject ke system prompt saat AI membalas.
 * Tipe: faq, shipping_rule, product_ref, promo, policy, custom.
 */
class KnowledgeBase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'connected_store_id',
        'type',
        'title',
        'content',
        'keywords',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'keywords'  => 'array',
            'is_active' => 'boolean',
            'priority'  => 'integer',
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(ConnectedStore::class, 'connected_store_id');
    }

    // ─── Label & Badge Helper ────────────────────────────────────

    public const TYPES = [
        'faq'           => ['label' => 'FAQ / Tanya Jawab', 'color' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'],
        'shipping_rule' => ['label' => 'Aturan Pengiriman', 'color' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
        'product_ref'   => ['label' => 'Referensi Produk',  'color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
        'promo'         => ['label' => 'Promo & Diskon',    'color' => 'bg-pink-500/10 text-pink-400 border-pink-500/20'],
        'policy'        => ['label' => 'Kebijakan Toko',    'color' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
        'custom'        => ['label' => 'Kustom / Catatan',  'color' => 'bg-purple-500/10 text-purple-400 border-purple-500/20'],
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeBadgeAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
    }

    // ─── Scope (filter query siap pakai) ──────────────────────────

    /** Ambil hanya entri yang aktif, diurutkan dari prioritas tertinggi */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority');
    }

    /** Filter berdasarkan tipe konten */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
