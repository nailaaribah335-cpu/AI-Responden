<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autentikasi') — Smart Receipt Scanner</title>
    <meta name="description" content="Login ke dashboard Smart Receipt Scanner untuk admin UMKM.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-[#070b14] text-slate-100 flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">

    {{-- Decorative Ambient Glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[550px] h-[550px] bg-gradient-to-b from-violet-600/20 to-cyan-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-20 w-80 h-80 bg-violet-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -right-20 w-80 h-80 bg-cyan-600/10 rounded-full blur-3xl"></div>
    </div>

    {{-- Container Utama --}}
    <div class="w-full max-w-lg z-10 relative my-8">

        {{-- Logo & Brand --}}
        <div class="text-center mb-6">
            <a href="/" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-500 to-cyan-500 flex items-center justify-center shadow-xl shadow-violet-600/30 group-hover:scale-105 transition duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-extrabold text-xl tracking-tight text-white">Receipt Scanner</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gradient-to-r from-violet-500/20 to-cyan-500/20 text-violet-300 border border-violet-500/30">SMART</span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Dashboard Admin UMKM</p>
                </div>
            </a>
        </div>

        {{-- Glassmorphic Card Form --}}
        <div class="glass-panel rounded-3xl p-7 md:p-9 shadow-2xl border-slate-800/90 relative overflow-hidden backdrop-blur-2xl">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-violet-500 via-indigo-500 to-cyan-500"></div>
            @yield('content')
        </div>

        {{-- Footer Trust Badge --}}
        <div class="text-center mt-6 text-xs text-slate-400 flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Data UMKM Terenkripsi & Aman</span>
        </div>

    </div>

</body>
</html>
