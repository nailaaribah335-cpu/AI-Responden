@extends('layouts.app')

@section('title', 'Knowledge Base (Otak AI)')
@section('page-title', 'Knowledge Base')
@section('page-subtitle', 'Kelola basis pengetahuan (FAQ, aturan kirim, produk, promo) untuk referensi AI')

@section('header-action')
    <a href="{{ route('knowledge-base.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Pengetahuan
    </a>
@endsection

@section('content')

{{-- ── Filter & Search Bar ───────────────────────────────────────── --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <form method="GET" action="{{ route('knowledge-base.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
        
        {{-- Search Input --}}
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Cari judul atau isi..."
                   class="w-full pl-9 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500">
        </div>

        {{-- Type Filter Pills --}}
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
            <a href="{{ route('knowledge-base.index', array_filter(['q' => request('q')])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ !request('type') ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                Semua
            </a>
            @foreach (\App\Models\KnowledgeBase::TYPES as $key => $info)
                <a href="{{ route('knowledge-base.index', array_filter(['type' => $key, 'q' => request('q')])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap {{ request('type') === $key ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                    {{ $info['label'] }}
                </a>
            @endforeach
        </div>

    </form>
</div>

{{-- ── Daftar Item Knowledge Base ───────────────────────────────── --}}
@if ($items->isEmpty())
    <div class="bg-gray-900 border border-dashed border-gray-800 rounded-2xl p-12 text-center">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 text-indigo-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-200 mb-1">Belum ada data pengetahuan</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto mb-5">
            Tambahkan FAQ, jam operasional, atau aturan promo agar AI dapat menjawab chat pembeli dengan akurat dan cepat.
        </p>
        <a href="{{ route('knowledge-base.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
            + Tambah Pengetahuan Pertama
        </a>
    </div>
@else
    <div class="grid gap-4">
        @foreach ($items as $item)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-gray-700 transition">
                
                {{-- Baris Atas: Badges & Aksi --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        
                        {{-- Tipe Konten --}}
                        <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-lg border {{ $item->type_badge }}">
                            {{ $item->type_label }}
                        </span>

                        {{-- Prioritas --}}
                        <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-lg bg-gray-800 text-gray-400 border border-gray-700">
                            Prioritas: P{{ $item->priority }}
                        </span>

                        {{-- Target Toko --}}
                        <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-lg bg-gray-800/80 text-gray-300">
                            @if ($item->store)
                                🏬 {{ $item->store->shop_name }} ({{ $item->store->platform_label }})
                            @else
                                🌐 Berlaku Semua Toko
                            @endif
                        </span>

                        {{-- Status Aktif / Nonaktif --}}
                        <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full {{ $item->is_active ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-gray-700/50 text-gray-500 border border-gray-700' }}">
                            {{ $item->is_active ? '● Aktif' : '○ Nonaktif' }}
                        </span>

                    </div>

                    {{-- Tombol Tindakan --}}
                    <div class="flex items-center gap-2">
                        {{-- Toggle Status --}}
                        <form action="{{ route('knowledge-base.toggle', $item) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 bg-gray-800 hover:bg-gray-700 text-gray-300 transition">
                                {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        {{-- Edit --}}
                        <a href="{{ route('knowledge-base.edit', $item) }}"
                           class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 bg-gray-800 hover:bg-gray-700 text-indigo-400 hover:text-indigo-300 transition">
                            Edit
                        </a>

                        {{-- Hapus --}}
                        <form action="{{ route('knowledge-base.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengetahuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Judul --}}
                <h3 class="text-base font-semibold text-white mb-2">{{ $item->title }}</h3>

                {{-- Konten --}}
                <div class="text-sm text-gray-300 leading-relaxed whitespace-pre-line bg-gray-950/60 border border-gray-800/80 rounded-xl p-3.5 mb-3">
                    {{ $item->content }}
                </div>

                {{-- Keywords Chips --}}
                @if (!empty($item->keywords) && count($item->keywords) > 0)
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-xs text-gray-500 mr-1">Kata Kunci:</span>
                        @foreach ($item->keywords as $keyword)
                            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-md border border-gray-700/60">
                                #{{ $keyword }}
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>
        @endforeach
    </div>

    {{-- Pagination Links --}}
    <div class="mt-6">
        {{ $items->links() }}
    </div>
@endif

@endsection
