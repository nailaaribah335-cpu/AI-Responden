<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AI Responder') — AI Responder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Layout dasar yang dipakai oleh semua halaman auth (login & register) --}}
<body class="h-full bg-gray-950 text-white flex items-center justify-center p-4">

    {{-- Card container utama --}}
    <div class="w-full max-w-md">

        {{-- Logo & Judul --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15m-6.75-5.25a2.25 2.25 0 00-2.25 2.25v.75m-1.5-4.5H12m0 0h1.5M12 10.5v9"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold">AI Responder</h1>
            <p class="text-gray-400 text-sm mt-1">Platform balasan otomatis multi-marketplace</p>
        </div>

        {{-- Card Konten (form login/register) --}}
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8">
            @yield('content')
        </div>

    </div>

</body>
</html>
