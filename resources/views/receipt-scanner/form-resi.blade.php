@extends('layouts.app')

@section('title', 'Cari Nomor Resi — Receipt Scanner')
@section('page-title', 'Pencarian Resi')
@section('page-subtitle', 'Input nomor resi manual')

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
<div class="max-w-2xl mx-auto">
    <div class="glass-panel p-8 rounded-2xl border border-slate-800/60">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-white mb-2">Cari Data Pesanan via Resi</h2>
            <p class="text-sm text-slate-400">Masukkan nomor resi untuk mencari data pesanan dari sistem eksternal atau database, dan memproses auto-entry.</p>
        </div>

        <form action="{{ route('receipt-scanner.cari-resi') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="nomor_resi" class="block text-sm font-semibold text-slate-300 mb-2">Nomor Resi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="nomor_resi" 
                           name="nomor_resi" 
                           required
                           autofocus
                           placeholder="Contoh: JX1234567890"
                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                @error('nomor_resi')
                    <p class="text-rose-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Cari Resi Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
