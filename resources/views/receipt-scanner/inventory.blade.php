@extends('layouts.app')

@section('title', 'Inventaris Produk — Receipt Scanner')
@section('page-title', 'Inventaris Stok')
@section('page-subtitle', 'Kelola stok produk UMKM')

@section('header-action')
    <a href="{{ route('receipt-scanner.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/60 border border-slate-700/50 hover:border-indigo-500/40 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Scanner
    </a>
@endsection

@section('content')
<div x-data="{ showForm: false }" class="space-y-6">

    {{-- ── Tombol Tambah Produk ────────────────────────────────────── --}}
    <div class="flex justify-end">
        <button @click="showForm = !showForm"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/40 hover:scale-[1.01] transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span x-text="showForm ? 'Tutup Form' : 'Tambah Produk'"></span>
        </button>
    </div>

    {{-- ── Form Tambah Produk ──────────────────────────────────────── --}}
    <div x-show="showForm" x-transition class="rounded-2xl bg-[#0d1321]/80 border border-indigo-500/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 bg-indigo-500/5">
            <h3 class="text-sm font-bold text-indigo-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Tambah Produk Baru
            </h3>
        </div>
        <form action="{{ route('receipt-scanner.inventory.store') }}" method="POST" class="p-6" id="form-tambah-produk">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kode SKU <span class="text-rose-400">*</span></label>
                    <input type="text" name="sku" required value="{{ old('sku') }}" placeholder="SKU-001"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Produk <span class="text-rose-400">*</span></label>
                    <input type="text" name="nama_produk" required value="{{ old('nama_produk') }}" placeholder="Nama produk"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Stok Awal <span class="text-rose-400">*</span></label>
                    <input type="number" name="stok" required value="{{ old('stok', 0) }}" min="0"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Batas Minimum Restock <span class="text-rose-400">*</span></label>
                    <input type="number" name="stok_minimum" required value="{{ old('stok_minimum', 5) }}" min="0"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Harga Satuan (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" name="harga_satuan" required value="{{ old('harga_satuan', 0) }}" min="0" step="0.01"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Satuan <span class="text-rose-400">*</span></label>
                    <select name="satuan" required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/50 text-sm text-white focus:ring-2 focus:ring-indigo-500/40 outline-none transition">
                        <option value="pcs" {{ old('satuan') === 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="kg" {{ old('satuan') === 'kg' ? 'selected' : '' }}>Kg</option>
                        <option value="liter" {{ old('satuan') === 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="pack" {{ old('satuan') === 'pack' ? 'selected' : '' }}>Pack</option>
                        <option value="box" {{ old('satuan') === 'box' ? 'selected' : '' }}>Box</option>
                        <option value="lusin" {{ old('satuan') === 'lusin' ? 'selected' : '' }}>Lusin</option>
                    </select>
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 hover:scale-[1.01] transition-all duration-200 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>

    {{-- ── Tabel Inventaris ────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0d1321]/80 border border-slate-800/60 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Daftar Produk Inventaris</h3>
                <p class="text-[11px] text-slate-400">Stok otomatis terpotong saat pesanan diselesaikan oleh timer</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="tabel-inventaris">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">SKU</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Nama Produk</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-center">Stok</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-center">Min. Restock</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-right">Harga</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Satuan</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400 text-center">Status</th>
                        <th class="px-6 py-3 text-[10px] uppercase tracking-wider font-bold text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse($inventories as $inv)
                        <tr class="hover:bg-slate-800/20 transition group" x-data="{ editing: false }">
                            {{-- Mode View --}}
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-xs text-slate-300 font-mono">{{ $inv->sku }}</td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-xs text-white font-medium">{{ $inv->nama_produk }}</td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-center">
                                    <span class="text-sm font-bold {{ $inv->needsRestock() ? 'text-red-400' : 'text-white' }}">{{ $inv->stok }}</span>
                                </td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-xs text-slate-400 text-center">{{ $inv->stok_minimum }}</td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-xs text-slate-300 text-right font-mono">Rp {{ number_format($inv->harga_satuan, 0, ',', '.') }}</td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-xs text-slate-400">{{ $inv->satuan }}</td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3 text-center">
                                    @if($inv->needsRestock())
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-red-500/15 text-red-400 border border-red-500/30 animate-pulse">⚠ Restock</span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Aman</span>
                                    @endif
                                </td>
                            </template>
                            <template x-if="!editing">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                        <button @click="editing = true" class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('receipt-scanner.inventory.destroy', $inv) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition" title="Hapus">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </template>

                            {{-- Mode Edit (inline) --}}
                            <template x-if="editing">
                                <td colspan="8" class="px-6 py-4">
                                    <form action="{{ route('receipt-scanner.inventory.update', $inv) }}" method="POST" class="flex items-end gap-3 flex-wrap">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Stok</label>
                                            <input type="number" name="stok" value="{{ $inv->stok }}" min="0"
                                                   class="w-24 px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-indigo-500/30 outline-none" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Min. Restock</label>
                                            <input type="number" name="stok_minimum" value="{{ $inv->stok_minimum }}" min="0"
                                                   class="w-24 px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-indigo-500/30 outline-none" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Harga</label>
                                            <input type="number" name="harga_satuan" value="{{ $inv->harga_satuan }}" min="0" step="0.01"
                                                   class="w-32 px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700/40 text-xs text-white focus:ring-2 focus:ring-indigo-500/30 outline-none" />
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-500 transition">Simpan</button>
                                            <button type="button" @click="editing = false" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-600 transition">Batal</button>
                                        </div>
                                    </form>
                                </td>
                            </template>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800/50 flex items-center justify-center mb-4">
                                    <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">Belum ada produk</p>
                                <p class="text-xs text-slate-500 mt-1">Klik "Tambah Produk" untuk memulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventories->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/50">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
