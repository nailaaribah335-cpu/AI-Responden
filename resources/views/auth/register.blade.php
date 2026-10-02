@extends('layouts.auth')

@section('title', 'Daftar Akun Seller')

@section('content')
    <div class="mb-6">
        <h2 class="font-heading text-xl md:text-2xl font-bold text-white tracking-tight">Mulai Otomasi Toko Anda 🚀</h2>
        <p class="text-xs md:text-sm text-slate-400 mt-1">
            Sudah terdaftar sebagai seller?
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition underline underline-offset-4">
                Masuk ke akun &rarr;
            </a>
        </p>
    </div>

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Nama Pemilik --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Nama Anda"
                           class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('name') border-rose-500/80 @enderror">
                </div>
                @error('name')
                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Toko --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Toko / Brand</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="store_name"
                           value="{{ old('store_name') }}"
                           required
                           placeholder="Toko Fashion Naila"
                           class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('store_name') border-rose-500/80 @enderror">
                </div>
                @error('store_name')
                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Toko</label>
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
                       placeholder="seller@tokoonline.com"
                       class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('email') border-rose-500/80 @enderror">
            </div>
            @error('email')
                <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Password --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input type="password"
                           name="password"
                           required
                           placeholder="Min. 8 karakter"
                           class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('password') border-rose-500/80 @enderror">
                </div>
                @error('password')
                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ulangi Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <input type="password"
                           name="password_confirmation"
                           required
                           placeholder="Ketik ulang password"
                           class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs md:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>
        </div>

        {{-- Checklist Keunggulan --}}
        <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-[11px] text-slate-400 space-y-1">
            <p class="flex items-center gap-1.5 text-slate-300 font-medium">
                <span class="text-emerald-400">✓</span> Respons otomatis kilat &lt; 10ms tanpa nunggu
            </p>
            <p class="flex items-center gap-1.5 text-slate-300 font-medium">
                <span class="text-indigo-400">✓</span> Terintegrasi Bot Telegram & Marketplace Multi-Toko
            </p>
            <p class="flex items-center gap-1.5 text-slate-300 font-medium">
                <span class="text-purple-400">✓</span> Fitur Human Takeover kapan saja seller ingin balas langsung
            </p>
        </div>

        {{-- Tombol Daftar --}}
        <button type="submit"
                class="w-full mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-600/25 hover:scale-[1.01] active:scale-[0.99] text-xs md:text-sm flex items-center justify-center gap-2">
            <span>Daftar Akun Seller Sekarang</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
    </form>
@endsection

