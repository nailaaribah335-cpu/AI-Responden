<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0f172a; color: #e2e8f0; padding: 32px; }
        .card { background: #1e293b; border-radius: 16px; padding: 32px; max-width: 520px; margin: 0 auto; border: 1px solid #334155; }
        .badge { display: inline-block; background: #f59e0b22; color: #fbbf24; border: 1px solid #f59e0b55; padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; }
        .heading { font-size: 20px; font-weight: 700; margin: 16px 0 8px; color: #f1f5f9; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #334155; font-size: 14px; }
        .info-label { color: #94a3b8; }
        .info-value { color: #e2e8f0; font-weight: 600; }
        .alert-box { background: #ef444422; border: 1px solid #ef444455; border-radius: 12px; padding: 16px; margin-top: 20px; text-align: center; color: #fca5a5; font-size: 14px; }
        .footer { text-align: center; color: #64748b; font-size: 12px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">⚠️ PERINGATAN RESTOCK</span>

        <h1 class="heading">Stok Produk Rendah</h1>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">
            Sistem mendeteksi stok produk berikut mendekati habis dan perlu segera di-restock.
        </p>

        <div class="info-row">
            <span class="info-label">Nama Produk</span>
            <span class="info-value">{{ $inventory->nama_produk }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">SKU</span>
            <span class="info-value">{{ $inventory->sku }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stok Tersisa</span>
            <span class="info-value" style="color: #ef4444;">{{ $inventory->stok }} {{ $inventory->satuan }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Batas Minimum</span>
            <span class="info-value">{{ $inventory->stok_minimum }} {{ $inventory->satuan }}</span>
        </div>
        <div class="info-row" style="border-bottom: none;">
            <span class="info-label">Pesanan Terkait</span>
            <span class="info-value">#{{ $order->id }} — {{ $order->nama_pelanggan }}</span>
        </div>

        <div class="alert-box">
            <strong>Segera lakukan restock!</strong><br>
            Stok akan habis jika tidak segera diisi ulang.
        </div>

        <p class="footer">
            Email ini dikirim otomatis oleh sistem Smart Receipt Scanner.<br>
            {{ config('app.name') }} — {{ now()->format('d M Y H:i') }}
        </p>
    </div>
</body>
</html>
