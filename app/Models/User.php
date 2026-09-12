<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model Seller — satu-satunya role pengguna di aplikasi ini.
 * Memiliki banyak toko, knowledge base, dan percakapan.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'store_name',
        'email',
        'phone',
        'password',
        'ai_provider',
        'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ─── Relasi ────────────────────────────────────────────────────

    /** Semua toko marketplace yang sudah dihubungkan */
    public function stores()
    {
        return $this->hasMany(ConnectedStore::class);
    }

    /** Knowledge base (FAQ, aturan, produk) milik seller ini */
    public function knowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    /** Semua percakapan dari semua toko milik seller ini */
    public function conversations()
    {
        return $this->hasManyThrough(Conversation::class, ConnectedStore::class);
    }
}
