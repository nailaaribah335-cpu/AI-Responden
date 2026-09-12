@extends('layouts.app')

@section('title', 'Hubungkan Toko')
@section('page-title', 'Hubungkan Toko Baru')
@section('page-subtitle', 'Pilih marketplace dan masukkan token akses API toko kamu')

@section('header-action')
    <a href="{{ route('stores.index') }}" class="text-sm text-gray-400 hover:text-white transition">
        ← Kembali
    </a>
@endsection

@section('content')

<div class="max-w-xl mt-2">

    {{-- Info Developer Mode --}}
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-sm text-amber-400 mb-6">
        <strong>Mode Manual (Developer)</strong> — Masukkan token API secara langsung.
        OAuth redirect otomatis akan diimplementasikan pada tahap berikutnya.
    </div>

    <form action="{{ route('stores.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Pilih Platform --}}
        <div>
            <label class="block text-sm text-gray-300 mb-3">Platform Marketplace</label>
            <div class="grid grid-cols-3 gap-3">
                @php
                    $platforms = [
                        ['value' => 'shopee', 'label' => 'Shopee',     'color' => 'peer-checked:border-orange-500 peer-checked:bg-orange-500/10'],
                        ['value' => 'lazada', 'label' => 'Lazada',     'color' => 'peer-checked:border-blue-500 peer-checked:bg-blue-500/10'],
                        ['value' => 'tiktok', 'label' => 'TikTok Shop','color' => 'peer-checked:border-pink-500 peer-checked:bg-pink-500/10'],
                    ];
                @endphp
                @foreach ($platforms as $p)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="platform" value="{{ $p['value'] }}"
                               class="peer sr-only" {{ old('platform') === $p['value'] ? 'checked' : '' }} required>
                        <span class="block text-center py-3 px-2 rounded-xl border border-gray-700 text-sm
                                     transition {{ $p['color'] }} peer-checked:font-semibold">
                            {{ $p['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('platform')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ID Toko --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">ID Toko (Shop ID)</label>
            <input type="text" name="shop_id" value="{{ old('shop_id') }}" required
                   placeholder="contoh: 12345678"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shop_id') border-red-500 @enderror">
            @error('shop_id')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Toko --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Nama Toko</label>
            <input type="text" name="shop_name" value="{{ old('shop_name') }}" required
                   placeholder="contoh: Toko Elektronik Murah"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shop_name') border-red-500 @enderror">
            @error('shop_name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Access Token --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Access Token</label>
            <textarea name="access_token" rows="3" required
                      placeholder="Token OAuth dari dashboard developer marketplace"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm font-mono
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('access_token') border-red-500 @enderror">{{ old('access_token') }}</textarea>
            <p class="text-gray-600 text-xs mt-1">Token ini akan dienkripsi sebelum disimpan.</p>
            @error('access_token')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Refresh Token (Opsional) --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">
                Refresh Token <span class="text-gray-600">(opsional)</span>
            </label>
            <input type="text" name="refresh_token" value="{{ old('refresh_token') }}"
                   placeholder="Untuk memperbarui access token secara otomatis"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm font-mono
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-3 rounded-xl transition text-sm">
            Hubungkan Toko
        </button>

    </form>
</div>

@endsection
