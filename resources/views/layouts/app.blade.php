<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — AI Responder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Layout utama aplikasi dengan sidebar --}}
<body class="h-full bg-gray-950 text-white flex">

    {{-- ── Sidebar Kiri ─────────────────────────────────────────── --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col">

        {{-- Logo --}}
        <div class="p-5 border-b border-gray-800">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                    </svg>
                </div>
                <span class="font-bold text-lg">AI Responder</span>
            </a>
        </div>

        {{-- Navigasi Utama --}}
        <nav class="flex-1 p-4 space-y-1">
            @php
                // Helper: tambahkan kelas aktif jika URL sesuai
                $navClass = fn($route) => request()->routeIs($route)
                    ? 'flex items-center gap-3 px-3 py-2 rounded-lg bg-indigo-600 text-white font-medium'
                    : 'flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition';
            @endphp

            <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('stores.index') }}" class="{{ $navClass('stores.*') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Toko Saya
            </a>

            <a href="{{ route('knowledge-base.index') }}" class="{{ $navClass('knowledge-base.*') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Knowledge Base
            </a>

            <a href="{{ route('inbox.index') }}" class="{{ $navClass('inbox.*') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Inbox
            </a>
        </nav>

        {{-- Info Seller di Bawah Sidebar --}}
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-indigo-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->store_name ?? 'Lengkapi profil' }}</p>
                </div>
            </div>
            {{-- Tombol Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full text-left text-xs text-gray-500 hover:text-red-400 transition px-1 py-1">
                    Keluar dari akun
                </button>
            </form>
        </div>

    </aside>

    {{-- ── Konten Utama ──────────────────────────────────────────── --}}
    <main class="flex-1 overflow-auto">

        {{-- Header halaman --}}
        <div class="border-b border-gray-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">@yield('page-title', 'Dashboard')</h1>
                <p class="text-sm text-gray-400 mt-0.5">@yield('page-subtitle')</p>
            </div>
            @yield('header-action')
        </div>

        {{-- Flash message (sukses / error) --}}
        <div class="px-8 pt-5">
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Isi halaman yang spesifik --}}
        <div class="px-8 pb-8">
            @yield('content')
        </div>

    </main>

</body>
</html>
