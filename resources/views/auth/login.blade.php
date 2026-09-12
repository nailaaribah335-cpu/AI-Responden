@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h2 class="text-xl font-bold mb-1">Selamat Datang Kembali</h2>
    <p class="text-gray-400 text-sm mb-6">Belum punya akun?
        <a href="{{ route('register') }}" class="text-indigo-400 hover:underline">Daftar gratis</a>
    </p>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label class="block text-sm text-gray-300 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
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
                   placeholder="Password kamu"
                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" id="remember" name="remember"
                   class="w-4 h-4 accent-indigo-500">
            <label for="remember" class="text-sm text-gray-400">Ingat saya</label>
        </div>

        <button type="submit"
                class="w-full mt-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold
                       py-2.5 rounded-xl transition text-sm">
            Masuk ke Dashboard
        </button>
    </form>
@endsection
