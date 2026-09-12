<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnectedStoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

// ─── Public Routes (Guest Only) ────────────────────────────────────
// Middleware 'guest' = redirect ke dashboard jika sudah login
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ─── Protected Routes (Seller Must Be Logged In) ───────────────────
// Middleware 'auth' = redirect ke /login jika belum login
Route::middleware('auth')->group(function () {

    // Redirect root ke dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen toko marketplace
    Route::prefix('stores')->name('stores.')->group(function () {
        Route::get('/',                              [ConnectedStoreController::class, 'index'])->name('index');
        Route::get('/create',                        [ConnectedStoreController::class, 'create'])->name('create');
        Route::post('/',                             [ConnectedStoreController::class, 'store'])->name('store');
        Route::delete('/{store}',                    [ConnectedStoreController::class, 'destroy'])->name('destroy');
        Route::patch('/{store}/toggle-ai',           [ConnectedStoreController::class, 'toggleAi'])->name('toggle-ai');
    });

    // Manajemen pengetahuan AI (Knowledge Base)
    Route::prefix('knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/',                              [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/create',                        [KnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/',                             [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{knowledgeBase}/edit',          [KnowledgeBaseController::class, 'edit'])->name('edit');
        Route::put('/{knowledgeBase}',               [KnowledgeBaseController::class, 'update'])->name('update');
        Route::delete('/{knowledgeBase}',            [KnowledgeBaseController::class, 'destroy'])->name('destroy');
        Route::patch('/{knowledgeBase}/toggle',      [KnowledgeBaseController::class, 'toggleStatus'])->name('toggle');
    });

    // Centralized Inbox & Human Takeover
    Route::prefix('inbox')->name('inbox.')->group(function () {
        Route::get('/',                              [InboxController::class, 'index'])->name('index');
        Route::post('/{conversation}/reply',         [InboxController::class, 'reply'])->name('reply');
        Route::patch('/{conversation}/toggle-ai',    [InboxController::class, 'toggleAi'])->name('toggle-ai');
        Route::post('/simulate',                     [InboxController::class, 'simulateMessage'])->name('simulate');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ─── Marketplace Webhook Endpoints (Bypass CSRF) ───────────────────
Route::prefix('api')->withoutMiddleware([ValidateCsrfToken::class])->group(function () {
    Route::post('/incoming-chat',    [WebhookController::class, 'handleIncomingChat'])->name('api.incoming-chat');
    Route::post('/webhook/shopee',   [WebhookController::class, 'handleShopee'])->name('webhook.shopee');
    Route::post('/webhook/tiktok',   [WebhookController::class, 'handleTikTok'])->name('webhook.tiktok');
    Route::post('/webhook/lazada',   [WebhookController::class, 'handleLazada'])->name('webhook.lazada');
});

