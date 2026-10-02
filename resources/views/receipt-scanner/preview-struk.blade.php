@extends('layouts.app')

@section('title', 'Preview Struk — Receipt Scanner')
@section('page-title', 'Preview Hasil Scan')
@section('page-subtitle', 'Verifikasi data sebelum disimpan')

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
<div x-data="previewStruk()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Kolom Kiri: Preview Gambar Struk --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-800/50">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Gambar Struk
                </h3>
            </div>
            <div class="p-4">
                @if($gambar_struk)
                    <img src="{{ Storage::url($gambar_struk) }}" alt="Struk" class="w-full rounded-xl border border-slate-700/50 shadow-lg" />
                @else
                    <div class="w-full h-48 rounded-xl bg-slate-900/50 flex items-center justify-center">
                        <p class="text-xs text-slate-500">Tidak ada gambar</p>
                    </div>
                @endif
            </div>

            {{-- OCR Confidence Info --}}
            <div class="px-4 pb-4">
                <div class="p-3 rounded-xl bg-violet-500/5 border border-violet-500/15">
                    <p class="text-[10px] uppercase tracking-wider font-bold text-violet-400/70 mb-1">Hasil OCR</p>
                    <p class="text-[11px] text-slate-400">Data di bawah ini di-extract otomatis dari struk. Mohon <span class="text-violet-300 font-semibold">verifikasi dan koreksi</span> jika ada kesalahan sebelum menyimpan.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Form Data Terextract --}}
    <div class="lg:col-span-2">
        <form action="{{ route('receipt-scanner.store-struk') }}" method="POST" id="form-preview-struk">
            @csrf
            <input type="hidden" name="gambar_struk" value="{{ $gambar_struk }}">
            <input type="hidden" name="ocr_raw" value="{{ json_encode($ocr_raw) }}">

            {{-- Data Utama --}}
            <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-slate-800/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">Data Pesanan</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">ID Transaksi</label>
                        <input type="text" name="id_transaksi" value="{{ old('id_transaksi', $data['id_transaksi'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/40 outline-none transition"
                               placeholder="INV-XXXXXXXX" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Pelanggan <span class="text-rose-400">*</span></label>
                        <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $data['nama_pelanggan'] ?? '') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $data['tanggal'] ?? '') }}" required
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Total Harga (Rp) <span class="text-rose-400">*</span></label>
                        <input type="number" name="total_harga" value="{{ old('total_harga', $data['total_harga'] ?? 0) }}" required step="0.01"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
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
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-emerald-500/30 outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Jumlah</label>
                                    <input type="number" :name="'items['+index+'][jumlah]'" x-model.number="item.jumlah" required min="1"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-emerald-500/30 outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" :name="'items['+index+'][harga_satuan]'" x-model.number="item.harga_satuan" required min="0" step="0.01"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-emerald-500/30 outline-none transition" />
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
                    Batal & Kembali
                </a>
                <button type="submit" id="btn-simpan-struk"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-sm font-bold shadow-lg shadow-violet-600/25 hover:shadow-violet-600/40 hover:scale-[1.01] transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Konfirmasi & Simpan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewStruk() {
    return {
        items: @json($items ?? [['nama_item' => '', 'jumlah' => 1, 'harga_satuan' => 0]]),

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
