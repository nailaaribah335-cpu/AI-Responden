@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <h2 class="text-xl font-bold mb-1">Buat Akun Seller</h2>
    <p class="text-gray-400 text-sm mb-6">Sudah punya akun?
        <a href="{{ route('login') }}" class="text-indigo-400 hover:underline">Masuk di sini</a>
    </p>

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Nama Pemilik --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="contoh: Budi Santoso"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Toko --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Nama Toko / Brand</label>
            <input type="text" name="store_name" value="{{ old('store_name') }}" required
                   placeholder="contoh: Toko Elektronik Murah"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('store_name') border-red-500 @enderror">
            @error('store_name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   placeholder="email@domain.com"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Password</label>
            <input type="password" name="password" required
                   placeholder="Minimal 8 karakter"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required
                   placeholder="Ulangi password"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit"
                class="w-full mt-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-xl transition text-sm">
            Daftar Sekarang
        </button>
    </form>
@endsection
