@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->id . ' — Receipt Scanner')
@section('page-title', 'Detail Pesanan')
@section('page-subtitle', '#' . $order->id . ' — ' . $order->nama_pelanggan)

@section('header-action')
    <a href="{{ route('receipt-scanner.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/60 border border-slate-700/50 hover:border-indigo-500/40 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- ── Status Banner ──────────────────────────────────────────── --}}
    <div class="rounded-2xl p-6 border
        {{ match($order->status) {
            'pending'    => 'bg-amber-500/5 border-amber-500/20',
            'selesai'    => 'bg-emerald-500/5 border-emerald-500/20',
            'dibatalkan' => 'bg-rose-500/5 border-rose-500/20',
            default      => 'bg-slate-500/5 border-slate-500/20',
        } }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($order->status === 'pending')
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/15 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-amber-300">Status: Pending (Timer Aktif)</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pesanan akan otomatis diselesaikan dalam 1 menit setelah input.</p>
                    </div>
                @elseif($order->status === 'selesai')
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-emerald-300">Status: Selesai</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Diselesaikan pada: {{ $order->selesai_at?->format('d M Y — H:i') }}</p>
                    </div>
                @else
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/15 flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-rose-300">Status: Dibatalkan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pesanan telah dibatalkan oleh admin.</p>
                    </div>
                @endif
            </div>

            @if($order->isPending())
                <form action="{{ route('receipt-scanner.cancel', $order) }}" method="POST" onsubmit="return confirm('Yakin batalkan pesanan ini?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs font-bold hover:bg-rose-500/20 transition flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan Pesanan
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Data Pesanan ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/50">
                <h3 class="text-sm font-bold text-white">Informasi Pesanan</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-y-5 gap-x-8">
                    @php
                        $fields = [
                            ['label' => 'ID Transaksi', 'value' => $order->id_transaksi ?? '-', 'mono' => true],
                            ['label' => 'Nomor Resi', 'value' => $order->nomor_resi ?? '-', 'mono' => true],
                            ['label' => 'Nama Pelanggan', 'value' => $order->nama_pelanggan],
                            ['label' => 'Tanggal', 'value' => $order->tanggal->format('d M Y')],
                            ['label' => 'Sumber Input', 'value' => $order->sumber_label],
                            ['label' => 'Total Harga', 'value' => 'Rp ' . number_format($order->total_harga, 0, ',', '.'), 'highlight' => true],
                            ['label' => 'Dibuat', 'value' => $order->created_at->format('d M Y — H:i:s')],
                            ['label' => 'Selesai', 'value' => $order->selesai_at?->format('d M Y — H:i:s') ?? 'Belum selesai'],
                        ];
                    @endphp
                    @foreach($fields as $field)
                        <div>
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500">{{ $field['label'] }}</p>
                            <p class="text-sm mt-0.5 {{ ($field['mono'] ?? false) ? 'font-mono' : '' }} {{ ($field['highlight'] ?? false) ? 'font-bold text-emerald-400' : 'font-semibold text-white' }}">
                                {{ $field['value'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sidebar: Struk Image --}}
        <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800/50">
                <h3 class="text-sm font-bold text-white">Gambar Struk</h3>
            </div>
            <div class="p-4">
                @if($order->gambar_struk)
                    <img src="{{ Storage::url($order->gambar_struk) }}" alt="Struk" class="w-full rounded-xl border border-slate-700/50" />
                @else
                    <div class="w-full h-36 rounded-xl bg-slate-900/50 flex items-center justify-center">
                        <p class="text-xs text-slate-500">Tidak ada gambar struk</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Tabel Items ────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50">
            <h3 class="text-sm font-bold text-white">Detail Item Pesanan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">#</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Nama Item</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Inventaris</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right">Jumlah</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right">Harga Satuan</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @foreach($order->items as $i => $item)
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="px-6 py-3 text-xs text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-6 py-3 text-xs text-white font-medium">{{ $item->nama_item }}</td>
                            <td class="px-6 py-3">
                                @if($item->inventory)
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold">
                                        {{ $item->inventory->nama_produk }} (Stok: {{ $item->inventory->stok }})
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-500">— Tidak tertaut</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-300 text-right font-mono">{{ $item->jumlah }}</td>
                            <td class="px-6 py-3 text-xs text-slate-300 text-right font-mono">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-xs text-white text-right font-mono font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-700/50 bg-slate-900/30">
                        <td colspan="5" class="px-6 py-3 text-xs font-bold text-slate-300 text-right">TOTAL</td>
                        <td class="px-6 py-3 text-sm font-bold text-emerald-400 text-right font-mono">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Restock Notifications (if any) ─────────────────────────── --}}
    @if($order->restockNotifications->count())
        <div class="rounded-2xl bg-[#0d1321]/80 border border-amber-500/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/50 bg-amber-500/5">
                <h3 class="text-sm font-bold text-amber-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Peringatan Restock dari Pesanan Ini
                </h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($order->restockNotifications as $notif)
                    <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-amber-500/5 border border-amber-500/10">
                        <div>
                            <p class="text-xs font-semibold text-amber-300">{{ $notif->inventory->nama_produk ?? 'Produk' }}</p>
                            <p class="text-[11px] text-slate-400">Stok tersisa: <span class="text-red-400 font-bold">{{ $notif->stok_tersisa }}</span> — Channel: {{ $notif->channel }}</p>
                        </div>
                        <span class="text-[10px] text-slate-500">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
