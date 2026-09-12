@extends('layouts.app')

@section('title', 'Toko Saya')
@section('page-title', 'Toko Saya')
@section('page-subtitle', 'Kelola semua toko marketplace yang terhubung')

@section('header-action')
    <a href="{{ route('stores.create') }}"
       class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-xl text-sm transition">
        + Hubungkan Toko Baru
    </a>
@endsection

@section('content')

    @if ($stores->isEmpty())
        {{-- Empty state --}}
        <div class="bg-gray-900 border border-dashed border-gray-700 rounded-2xl p-16 text-center mt-2">
            <p class="text-gray-400 text-lg font-medium mb-2">Belum ada toko terhubung</p>
            <p class="text-gray-600 text-sm mb-6">Hubungkan toko Shopee, Lazada, atau TikTok Shop kamu untuk mulai menggunakan AI Responder.</p>
            <a href="{{ route('stores.create') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-500 px-6 py-2.5 rounded-xl text-sm transition">
                Hubungkan Sekarang
            </a>
        </div>
    @else
        <div class="grid gap-3 mt-2">
            @foreach ($stores as $store)
            @php
                $platformColors = [
                    'shopee' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-400', 'dot' => 'bg-orange-500'],
                    'lazada' => ['bg' => 'bg-blue-500/20',   'text' => 'text-blue-400',   'dot' => 'bg-blue-500'],
                    'tiktok' => ['bg' => 'bg-pink-500/20',   'text' => 'text-pink-400',   'dot' => 'bg-pink-500'],
                ];
                $color = $platformColors[$store->platform] ?? ['bg' => 'bg-gray-700', 'text' => 'text-gray-400', 'dot' => 'bg-gray-500'];
            @endphp

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center gap-4">

                {{-- Icon Platform --}}
                <div class="w-12 h-12 rounded-xl {{ $color['bg'] }} flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-bold {{ $color['text'] }}">{{ strtoupper(substr($store->platform, 0, 2)) }}</span>
                </div>

                {{-- Info Toko --}}
                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">{{ $store->shop_name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full {{ $color['dot'] }}"></span>
                        <span class="text-xs text-gray-500">{{ $store->platform_label }} · ID: {{ $store->shop_id }}</span>
                    </div>
                </div>

                {{-- Status & Aksi --}}
                <div class="flex items-center gap-3 flex-shrink-0">

                    {{-- Toggle AI --}}
                    <form action="{{ route('stores.toggle-ai', $store) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit"
                                title="{{ $store->ai_enabled ? 'Nonaktifkan AI' : 'Aktifkan AI' }}"
                                class="text-xs px-3 py-1.5 rounded-lg border transition
                                    {{ $store->ai_enabled
                                        ? 'border-green-600 text-green-400 hover:bg-green-500/10'
                                        : 'border-gray-700 text-gray-500 hover:border-gray-600' }}">
                            AI {{ $store->ai_enabled ? 'ON' : 'OFF' }}
                        </button>
                    </form>

                    {{-- Hapus Toko --}}
                    <form action="{{ route('stores.destroy', $store) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin memutuskan toko {{ $store->shop_name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-500
                                       hover:border-red-600 hover:text-red-400 transition">
                            Putuskan
                        </button>
                    </form>

                </div>
            </div>
            @endforeach
        </div>
    @endif

@endsection
