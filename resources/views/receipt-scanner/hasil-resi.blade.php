@extends('layouts.app')

@section('title', 'Hasil Pencarian Resi — Receipt Scanner')
@section('page-title', 'Pencarian Resi')
@section('page-subtitle', $message ?? 'Hasil pencarian')

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
<div class="max-w-4xl mx-auto">

    @if($is_exists)
        {{-- ── Resi Ditemukan: Tampilkan data yang sudah ada ──────── --}}
        <div class="rounded-2xl bg-[#0d1321]/80 border border-emerald-500/20 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-800/50 bg-emerald-500/5 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-300">Data Pesanan Ditemukan</h3>
                    <p class="text-[11px] text-slate-400">Resi: <span class="font-mono text-cyan-300">{{ $order->nomor_resi }}</span></p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-5">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500">ID Transaksi</p>
                    <p class="text-sm font-semibold text-white mt-0.5 font-mono">{{ $order->id_transaksi ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Pelanggan</p>
                    <p class="text-sm font-semibold text-white mt-0.5">{{ $order->nama_pelanggan }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Tanggal</p>
                    <p class="text-sm font-semibold text-white mt-0.5">{{ $order->tanggal->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Total Harga</p>
                    <p class="text-sm font-bold text-emerald-400 mt-0.5">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($order->items->count())
                <div class="px-6 pb-6">
                    <p class="text-[10px] uppercase tracking-wider font-bold text-slate-500 mb-2">Items</p>
                    <div class="space-y-2">
                        @foreach($order->items as $item)
                            <div class="flex justify-between items-center px-4 py-2.5 rounded-xl bg-slate-900/50 border border-slate-800/30">
                                <div>
                                    <span class="text-xs text-white font-medium">{{ $item->nama_item }}</span>
                                    <span class="text-[11px] text-slate-500 ml-2">x{{ $item->jumlah }}</span>
                                </div>
                                <span class="text-xs text-slate-300 font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="px-6 pb-6 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-lg border {{ $order->status_badge }}">
                    {{ ucfirst($order->status) }}
                </span>
                <a href="{{ route('receipt-scanner.show', $order) }}"
                   class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                    Lihat Detail Lengkap →
                </a>
            </div>
        </div>

    @else
        {{-- ── Resi Tidak Ditemukan: Form Manual Entry ────────────── --}}
        <div class="rounded-2xl bg-[#0d1321]/80 border border-amber-500/20 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-800/50 bg-amber-500/5 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-300">Resi Tidak Ditemukan</h3>
                    <p class="text-[11px] text-slate-400">Resi <span class="font-mono text-cyan-300">{{ $nomor_resi }}</span> belum terdaftar. Silakan input manual.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('receipt-scanner.store-resi') }}" method="POST" x-data="formResi()" id="form-input-resi">
            @csrf
            <input type="hidden" name="nomor_resi" value="{{ $nomor_resi }}">

            {{-- Data Pesanan --}}
            <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-slate-800/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">Data Pesanan via Resi</h3>
                    <span class="ml-auto text-[10px] font-mono px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">{{ $nomor_resi }}</span>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">ID Transaksi</label>
                        <input type="text" name="id_transaksi" value="{{ old('id_transaksi') }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500/40 outline-none transition"
                               placeholder="Optional" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Pelanggan <span class="text-rose-400">*</span></label>
                        <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-cyan-500/40 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-cyan-500/40 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Total Harga (Rp) <span class="text-rose-400">*</span></label>
                        <input type="number" name="total_harga" value="{{ old('total_harga') }}" required step="0.01"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-cyan-500/40 outline-none transition" />
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white">Item Pesanan</h3>
                    </div>
                    <button type="button" @click="addItem()"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20 hover:bg-emerald-500/20 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-900/50 border border-slate-800/40">
                            <span class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center text-[11px] font-bold text-slate-400 flex-shrink-0 mt-1" x-text="index + 1"></span>
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Nama Item</label>
                                    <input type="text" :name="'items['+index+'][nama_item]'" x-model="item.nama_item" required
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-cyan-500/30 outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Jumlah</label>
                                    <input type="number" :name="'items['+index+'][jumlah]'" x-model.number="item.jumlah" required min="1"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-cyan-500/30 outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" :name="'items['+index+'][harga_satuan]'" x-model.number="item.harga_satuan" required min="0" step="0.01"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-cyan-500/30 outline-none transition" />
                                </div>
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                    class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition flex-shrink-0 mt-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('receipt-scanner.index') }}" class="text-xs text-slate-400 hover:text-slate-200 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Batal
                </a>
                <button type="submit" id="btn-simpan-resi"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-bold shadow-lg shadow-cyan-600/25 hover:shadow-cyan-600/40 hover:scale-[1.01] transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Pesanan via Resi
                </button>
            </div>
        </form>
    @endif
</div>

<script>
function formResi() {
    return {
        items: [{ nama_item: '', jumlah: 1, harga_satuan: 0 }],
        addItem() {
            this.items.push({ nama_item: '', jumlah: 1, harga_satuan: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        }
    }
}
</script>
@endsection
