@extends('layouts.app')

@section('title', 'Smart Receipt Scanner')
@section('page-title', 'Receipt Scanner')
@section('page-subtitle', 'Scan Struk & Auto-Entry Pesanan')

@section('header-action')
    <div class="flex items-center gap-2">
        {{-- Tombol Notifikasi Restock --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative p-2.5 rounded-xl bg-slate-800/60 border border-slate-700/50 hover:border-amber-500/40 transition group">
                <svg class="w-4 h-4 text-slate-400 group-hover:text-amber-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadAlerts->count() > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse">
                        {{ $unreadAlerts->count() }}
                    </span>
                @endif
            </button>
            {{-- Dropdown Notifikasi --}}
            <div x-show="open" @click.away="open = false" x-transition
                 class="absolute right-0 mt-2 w-80 bg-[#0f172a] border border-slate-700/60 rounded-2xl shadow-2xl shadow-black/40 overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-slate-700/50">
                    <p class="text-xs font-bold text-white flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Peringatan Restock
                    </p>
                </div>
                <div class="max-h-72 overflow-y-auto">
                    @forelse($unreadAlerts as $alert)
                        <div class="px-4 py-3 border-b border-slate-800/50 hover:bg-slate-800/40 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs font-semibold text-amber-300">{{ $alert->inventory->nama_produk ?? 'Produk' }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Sisa stok: <span class="text-red-400 font-bold">{{ $alert->stok_tersisa }}</span></p>
                                </div>
                                <form action="{{ route('receipt-scanner.notification.read', $alert) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-[10px] text-slate-500 hover:text-emerald-400 transition" title="Tandai dibaca">✓</button>
                                </form>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-xs text-slate-500">Tidak ada peringatan restock baru.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <a href="{{ route('receipt-scanner.inventory.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/60 border border-slate-700/50 hover:border-indigo-500/40 hover:text-indigo-300 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Inventaris
        </a>
    </div>
@endsection

@section('content')
<div x-data="receiptScanner()" class="space-y-6">

    {{-- ── Stat Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statCards = [
                ['label' => 'Total Pesanan',   'value' => $stats['total'],     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'indigo',  'from' => 'from-indigo-600/20', 'border' => 'border-indigo-500/20'],
                ['label' => 'Pending (Timer)',  'value' => $stats['pending'],   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber',   'from' => 'from-amber-600/20',  'border' => 'border-amber-500/20'],
                ['label' => 'Selesai',          'value' => $stats['selesai'],   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'from' => 'from-emerald-600/20','border' => 'border-emerald-500/20'],
                ['label' => 'Stok Alert',       'value' => $stats['stok_alert'],'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'rose', 'from' => 'from-rose-600/20', 'border' => 'border-rose-500/20'],
            ];
        @endphp

        @foreach($statCards as $card)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $card['from'] }} to-transparent border {{ $card['border'] }} p-5 group hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] uppercase tracking-wider font-bold text-{{ $card['color'] }}-400/80">{{ $card['label'] }}</p>
                        <p class="text-3xl font-extrabold text-white mt-1 font-heading">{{ number_format($card['value']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-{{ $card['color'] }}-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-{{ $card['color'] }}-500/5 rounded-full blur-2xl"></div>
            </div>
        @endforeach
    </div>

    {{-- ── Input Panels: Scan Struk & Input Resi ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Panel 1: Upload Scan Struk --}}
        <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-600 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-600/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Scan Struk (OCR)</h3>
                    <p class="text-[11px] text-slate-400">Upload gambar struk → otomatis di-extract</p>
                </div>
            </div>
            <form action="{{ route('receipt-scanner.upload-struk') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div x-data="{ fileName: '', preview: null }"
                     class="relative border-2 border-dashed border-slate-700/60 rounded-2xl p-8 text-center hover:border-violet-500/50 transition-colors duration-200 cursor-pointer group"
                     @click="$refs.fileInput.click()">

                    <input type="file" name="gambar_struk" accept="image/*" x-ref="fileInput" class="hidden"
                           @change="fileName = $event.target.files[0]?.name || ''; preview = URL.createObjectURL($event.target.files[0])"
                           id="upload-struk-input" required>

                    <template x-if="!preview">
                        <div>
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-violet-500/10 to-purple-500/10 border border-violet-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <svg class="w-7 h-7 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-300">Klik atau drag gambar struk ke sini</p>
                            <p class="text-[11px] text-slate-500 mt-1">Format: JPG, PNG, WEBP (max 5MB)</p>
                        </div>
                    </template>
                    <template x-if="preview">
                        <div>
                            <img :src="preview" class="max-h-40 mx-auto rounded-xl border border-slate-700/50 shadow-lg mb-3" />
                            <p class="text-xs text-violet-300 font-medium" x-text="fileName"></p>
                        </div>
                    </template>
                </div>

                <button type="submit" id="btn-scan-struk"
                        class="mt-5 w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-bold shadow-lg shadow-violet-600/25 hover:shadow-violet-600/40 hover:scale-[1.01] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Scan & Extract Data Struk
                </button>
            </form>
        </div>

        {{-- Panel 2: Input via Nomor Resi --}}
        <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-600/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Input via Resi</h3>
                    <p class="text-[11px] text-slate-400">Masukkan nomor resi → tarik data otomatis</p>
                </div>
            </div>
            <form action="{{ route('receipt-scanner.cari-resi') }}" method="POST" class="p-6">
                @csrf
                <label class="block text-xs font-semibold text-slate-300 mb-2">Nomor Resi / Tracking</label>
                <div class="flex gap-3">
                    <input type="text" name="nomor_resi" id="input-nomor-resi" required placeholder="Contoh: JNE1234567890"
                           class="flex-1 px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/50 transition outline-none" />
                    <button type="submit" id="btn-cari-resi"
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-bold shadow-lg shadow-cyan-600/25 hover:shadow-cyan-600/40 hover:scale-[1.01] transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari
                    </button>
                </div>
                <p class="mt-3 text-[11px] text-slate-500">
                    <svg class="w-3 h-3 inline text-cyan-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    Sistem akan mencari data pesanan berdasarkan nomor resi di database
                </p>
            </form>

            {{-- Info Timer --}}
            <div class="mx-6 mb-6 p-4 rounded-xl bg-slate-900/50 border border-slate-800/50">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-amber-300">Auto Timer 1 Menit</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Setelah data diinput, pesanan berstatus <span class="text-amber-400 font-bold">Pending</span>. Dalam 1 menit, sistem otomatis mengubah ke <span class="text-emerald-400 font-bold">Selesai</span>, memotong stok, dan memicu restock alert jika diperlukan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabel Riwayat Pesanan ─────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Riwayat Pesanan</h3>
                    <p class="text-[11px] text-slate-400">Semua pesanan dari scan struk & input resi</p>
                </div>
            </div>

            {{-- Filter & Search --}}
            <form action="{{ route('receipt-scanner.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" id="filter-status" class="px-3 py-2 rounded-lg bg-slate-900/80 border border-slate-700/50 text-xs text-slate-300 focus:ring-2 focus:ring-indigo-500/40 outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / resi..."
                       id="search-riwayat"
                       class="px-3 py-2 rounded-lg bg-slate-900/80 border border-slate-700/50 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/40 outline-none w-44" />
                <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-xs font-semibold text-indigo-300 hover:bg-indigo-600/30 transition">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tabel-riwayat">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">#</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">ID Transaksi</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Pelanggan</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Tanggal</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Total</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Sumber</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Status</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/20 transition group">
                            <td class="px-6 py-3.5 text-xs text-slate-500 font-mono">{{ $order->id }}</td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-mono font-semibold text-slate-300">{{ $order->id_transaksi ?? '-' }}</span>
                                @if($order->nomor_resi)
                                    <p class="text-[10px] text-slate-500 mt-0.5">Resi: {{ $order->nomor_resi }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-200 font-medium">{{ $order->nama_pelanggan }}</td>
                            <td class="px-6 py-3.5 text-xs text-slate-400">{{ $order->tanggal->format('d M Y') }}</td>
                            <td class="px-6 py-3.5 text-xs text-white font-semibold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                            <td class="px-6 py-3.5">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md
                                    {{ $order->sumber === 'scan_struk' ? 'bg-violet-500/15 text-violet-400 border border-violet-500/30' : 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/30' }}">
                                    {{ $order->sumber_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-lg border {{ $order->status_badge }}">
                                    @if($order->status === 'pending')
                                        <svg class="w-3 h-3 inline animate-spin mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @endif
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <a href="{{ route('receipt-scanner.show', $order) }}" class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition" title="Detail">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if($order->isPending())
                                        <form action="{{ route('receipt-scanner.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition" title="Batalkan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800/50 flex items-center justify-center mb-4">
                                    <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">Belum ada pesanan</p>
                                <p class="text-xs text-slate-500 mt-1">Upload struk atau masukkan resi untuk memulai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function receiptScanner() {
    return {
        // Alpine.js state jika dibutuhkan untuk interaksi
    }
}
</script>
@endsection
