<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // ─── Seller Identity ───────────────────────────────────────
            $table->string('name');                              // nama pemilik toko
            $table->string('store_name')->nullable();           // nama brand / toko
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();               // path foto profil
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // ─── AI & App Preferences ─────────────────────────────────
            $table->enum('ai_provider', ['gemini', 'openai'])->default('gemini');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
