@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name . '!')

@section('content')

{{-- ── Kartu Statistik ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-2">

    @php
        // Data statistik yang ditampilkan
        $cards = [
            ['label' => 'Toko Terhubung',    'value' => $stats['total_stores'],     'sub' => $stats['active_stores'] . ' aktif'],
            ['label' => 'Otak AI (Aturan)',   'value' => $stats['total_knowledge'],  'sub' => $stats['active_knowledge'] . ' aktif'],
            ['label' => 'Total Percakapan',  'value' => $stats['total_conversations'], 'sub' => 'Realtime'],
            ['label' => 'Pesan Belum Dibaca','value' => $stats['unread_messages'], 'sub' => 'Menunggu balasan'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
            <p class="text-xs font-medium text-gray-400 mb-1">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $card['sub'] }}</p>
        </div>
    @endforeach

</div>

{{-- ── Toko Terbaru ─────────────────────────────────────────────── --}}
<div class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-200">Toko Terhubung</h2>
        <a href="{{ route('stores.create') }}"
           class="text-sm bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-xl transition">
            + Hubungkan Toko
        </a>
    </div>

    @if ($recentStores->isEmpty())
        {{-- State kosong: belum ada toko --}}
        <div class="bg-gray-900 border border-dashed border-gray-700 rounded-2xl p-12 text-center">
            <p class="text-gray-500 mb-4">Belum ada toko yang dihubungkan.</p>
            <a href="{{ route('stores.create') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-500 px-6 py-2.5 rounded-xl text-sm transition">
                Hubungkan Toko Pertama
            </a>
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($recentStores as $store)
                {{-- Kartu per toko --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        {{-- Ikon platform --}}
                        @php
                            $colors = ['shopee' => 'bg-orange-500', 'lazada' => 'bg-blue-600', 'tiktok' => 'bg-gray-800'];
                        @endphp
                        <div class="w-10 h-10 rounded-xl {{ $colors[$store->platform] ?? 'bg-gray-700' }} flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($store->platform, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-medium">{{ $store->shop_name }}</p>
                            <p class="text-sm text-gray-500">{{ $store->platform_label }}</p>
                        </div>
                    </div>
                    {{-- Status AI --}}
                    <span class="text-xs px-3 py-1 rounded-full {{ $store->ai_enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-700 text-gray-400' }}">
                        AI {{ $store->ai_enabled ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
