<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReceiptScannerController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes (Guest Only) ────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ─── Protected Routes (Admin Must Be Logged In) ────────────────────
Route::middleware('auth')->group(function () {

    // Root → redirect ke Receipt Scanner dashboard
    Route::get('/', fn() => redirect()->route('receipt-scanner.index'));

    // ─── Smart Receipt Scanner & Auto-Entry ────────────────────────
    Route::prefix('receipt-scanner')->name('receipt-scanner.')->group(function () {
        // Dashboard & Riwayat
        Route::get('/',                              [ReceiptScannerController::class, 'index'])->name('index');

        // Fitur 1: Scan Struk (OCR)
        Route::post('/upload-struk',                 [ReceiptScannerController::class, 'uploadStruk'])->name('upload-struk');
        Route::post('/store-struk',                  [ReceiptScannerController::class, 'storeStruk'])->name('store-struk');

        // Fitur 2: Input via Resi
        Route::get('/resi',                          [ReceiptScannerController::class, 'formResi'])->name('form-resi');
        Route::post('/cari-resi',                    [ReceiptScannerController::class, 'cariResi'])->name('cari-resi');
        Route::post('/store-resi',                   [ReceiptScannerController::class, 'storeResi'])->name('store-resi');

        // Detail & Aksi
        Route::get('/{order}',                       [ReceiptScannerController::class, 'show'])->name('show');
        Route::patch('/{order}/cancel',              [ReceiptScannerController::class, 'cancel'])->name('cancel');

        // Inventaris / Stok
        Route::get('/kelola/inventaris',             [ReceiptScannerController::class, 'inventoryIndex'])->name('inventory.index');
        Route::post('/kelola/inventaris',             [ReceiptScannerController::class, 'inventoryStore'])->name('inventory.store');
        Route::put('/kelola/inventaris/{inventory}',  [ReceiptScannerController::class, 'inventoryUpdate'])->name('inventory.update');
        Route::delete('/kelola/inventaris/{inventory}',[ReceiptScannerController::class, 'inventoryDestroy'])->name('inventory.destroy');

        // Notifikasi
        Route::patch('/notifikasi/{notification}/read', [ReceiptScannerController::class, 'markNotificationRead'])->name('notification.read');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
