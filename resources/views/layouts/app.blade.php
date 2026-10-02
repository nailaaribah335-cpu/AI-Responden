<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Receipt Scanner') — UMKM Dashboard</title>
    <meta name="description" content="Smart Receipt Scanner & Auto-Entry untuk dashboard admin UMKM. Scan struk, input resi, auto-timer, anti double-input, dan restock alert.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-[#070b14] text-slate-100 flex overflow-hidden selection:bg-indigo-500 selection:text-white">

    {{-- Background subtle decorative ambient glow --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 left-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>
    </div>

    {{-- ── Sidebar Kiri ─────────────────────────────────────────── --}}
    <aside class="w-68 bg-[#0b101d]/90 backdrop-blur-xl border-r border-slate-800/80 flex flex-col z-20 relative flex-shrink-0">

        {{-- Logo Brand --}}
        <div class="p-5 border-b border-slate-800/80">
            <a href="{{ route('receipt-scanner.index') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 via-indigo-500 to-cyan-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-600/25 group-hover:scale-105 transition duration-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-extrabold text-base tracking-tight text-white">Receipt Scanner</span>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-gradient-to-r from-violet-500/20 to-cyan-500/20 text-violet-400 border border-violet-500/30">SMART</span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium truncate">UMKM Dashboard</p>
                </div>
            </a>
        </div>

        {{-- Status System --}}
        <div class="px-4 pt-4">
            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-200">Queue Worker Aktif</p>
                        <p class="text-[10px] text-slate-400">Auto-Timer 5 Menit</p>
                    </div>
                </div>
                <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">LIVE</span>
            </div>
        </div>

        {{-- Navigasi Utama --}}
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-300 px-3 pt-2 pb-1">Menu Utama</p>

            @php
                $navItem = function($route, $iconSvg, $label, $badge = null) {
                    $active = request()->routeIs($route . '*');
                    $baseClass = "flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition duration-150 group ";
                    if ($active) {
                        $classes = $baseClass . "bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-md shadow-indigo-600/20";
                    } else {
                        $classes = $baseClass . "text-slate-400 hover:text-slate-100 hover:bg-slate-800/60";
                    }

                    $badgeHtml = $badge ? "<span class='text-xs px-2 py-0.5 rounded-full font-bold " . ($active ? "bg-white/20 text-white" : "bg-indigo-500/10 text-indigo-400 border border-indigo-500/20") . "'>{$badge}</span>" : "";

                    return "<a href='" . route($route) . "' class='{$classes}'>
                                <div class='flex items-center gap-3'>
                                    {$iconSvg}
                                    <span>{$label}</span>
                                </div>
                                {$badgeHtml}
                            </a>";
                };
            @endphp

            {!! $navItem('receipt-scanner.index', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>', 'Dashboard Scanner') !!}

            {!! $navItem('receipt-scanner.inventory.index', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'Inventaris Stok') !!}

            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-300 px-3 pt-4 pb-1">Input Cepat</p>

            {!! $navItem('receipt-scanner.form-resi', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>', 'Input via Resi') !!}
        </nav>

        {{-- Info User di Bawah Sidebar --}}
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/40">
            <div class="flex items-center gap-3 mb-3 p-2 rounded-xl bg-slate-800/50 border border-slate-700/50">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-cyan-600 flex items-center justify-center text-sm font-bold text-white shadow-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->store_name ?? 'Admin UMKM' }}</p>
                </div>
            </div>

            {{-- Tombol Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 text-xs text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition px-3 py-2 rounded-lg font-medium border border-transparent hover:border-red-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar Akun
                </button>
            </form>
        </div>

    </aside>

    {{-- ── Konten Utama ──────────────────────────────────────────── --}}
    <main class="flex-1 overflow-auto flex flex-col z-10 relative">

        {{-- Top App Bar --}}
        <header class="h-18 border-b border-slate-800/80 bg-[#090e1a]/80 backdrop-blur-md px-8 flex items-center justify-between sticky top-0 z-30 flex-shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-heading text-lg font-bold text-white tracking-tight">@yield('page-title', 'Receipt Scanner')</h1>
                    <span class="text-xs text-slate-500">•</span>
                    <span class="text-xs font-medium text-slate-400">@yield('page-subtitle')</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-action')
            </div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-8 pt-4">
            @if (session('success'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2.5 shadow-lg shadow-emerald-500/5"
                     x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 6000)">
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-300 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs shadow-lg shadow-rose-500/5"
                     x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-rose-400/60 hover:text-rose-300 transition flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Content View Body --}}
        <div class="px-8 pb-10 flex-1">
            @yield('content')
        </div>

    </main>

</body>
</html>
