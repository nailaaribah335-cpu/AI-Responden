@extends('layouts.auth')

@section('title', 'Masuk Akun')

@section('content')
    <div class="mb-6">
        <h2 class="font-heading text-xl md:text-2xl font-bold text-white tracking-tight">Selamat Datang Kembali 👋</h2>
        <p class="text-xs md:text-sm text-slate-400 mt-1">
            Belum punya akun admin?
            <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition underline underline-offset-4">
                Daftar akun baru &rarr;
            </a>
        </p>
    </div>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       placeholder="nama@toko.com"
                       class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('email') border-rose-500/80 @enderror">
            </div>
            @error('email')
                <p class="text-rose-400 text-[11px] mt-1.5 flex items-center gap-1">
                    <span>⚠️</span> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-300">Kata Sandi</label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input type="password"
                       id="loginPassword"
                       name="password"
                       required
                       placeholder="Masukkan password akun Anda"
                       class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-10 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                <button type="button"
                        onclick="const p = document.getElementById('loginPassword'); p.type = p.type === 'password' ? 'text' : 'password';"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Remember Me & Info --}}
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox"
                       id="remember"
                       name="remember"
                       class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                <span class="text-xs text-slate-400">Ingat sesi saya</span>
            </label>
        </div>

        {{-- Tombol Submit --}}
        <button type="submit"
                class="w-full mt-3 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-600/25 hover:scale-[1.01] active:scale-[0.99] text-xs md:text-sm flex items-center justify-center gap-2">
            <span>Masuk ke Dashboard</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
    </form>
@endsection

