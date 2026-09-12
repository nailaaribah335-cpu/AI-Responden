@extends('layouts.app')

@section('title', 'Centralized Inbox — AI Responder')
@section('page-title', 'Centralized Inbox')
@section('page-subtitle', 'Pantau dan kelola percakapan dari semua toko marketplace dalam satu layar')

@section('header-action')
    <button type="button"
            onclick="document.getElementById('simulatorModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
        </svg>
        ⚡ Simulasi Chat Pembeli
    </button>
@endsection

@section('content')

{{-- ── Container Dua Kolom Chat ─────────────────────────────────── --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden flex flex-col md:flex-row" style="height: calc(100vh - 180px); min-height: 540px;">

    {{-- ── KOLOM KIRI: Daftar Percakapan ────────────────────────── --}}
    <div class="w-full md:w-80 lg:w-96 border-b md:border-b-0 md:border-r border-gray-800 flex flex-col bg-gray-900/60">
        
        {{-- Filter & Search Header --}}
        <div class="p-3.5 border-b border-gray-800 space-y-2.5">
            {{-- Search Bar --}}
            <form method="GET" action="{{ route('inbox.index') }}">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari pembeli / pesan..."
                           class="w-full pl-9 pr-3 py-1.5 bg-gray-800/90 border border-gray-700/80 rounded-xl text-xs text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500">
                </div>
            </form>

            {{-- Filter Status Pills --}}
            <div class="flex items-center gap-1 overflow-x-auto pb-1 text-xs">
                <a href="{{ route('inbox.index') }}"
                   class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ !request('ai_status') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white bg-gray-800/60' }}">
                    Semua
                </a>
                <a href="{{ route('inbox.index', ['ai_status' => 'ai_active']) }}"
                   class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ request('ai_status') === 'ai_active' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white bg-gray-800/60' }}">
                    🤖 AI Aktif
                </a>
                <a href="{{ route('inbox.index', ['ai_status' => 'human_takeover']) }}"
                   class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ request('ai_status') === 'human_takeover' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white bg-gray-800/60' }}">
                    👤 Human
                </a>
            </div>
        </div>

        {{-- Daftar Kontak Chat --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-800/60">
            @forelse ($conversations as $conv)
                @php
                    $isActive = $activeConversation && $activeConversation->id === $conv->id;
                    $platformColors = [
                        'shopee' => 'bg-orange-500 text-white',
                        'lazada' => 'bg-blue-600 text-white',
                        'tiktok' => 'bg-gray-800 text-gray-200 border border-gray-700',
                    ];
                @endphp
                <a href="{{ route('inbox.index', array_merge(request()->query(), ['c' => $conv->id])) }}"
                   class="block p-3.5 transition {{ $isActive ? 'bg-indigo-600/15 border-l-4 border-indigo-500' : 'hover:bg-gray-800/50' }}">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        
                        {{-- Nama & Platform --}}
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xs px-1.5 py-0.5 rounded font-bold uppercase tracking-wider {{ $platformColors[$conv->platform] ?? 'bg-gray-700' }}">
                                {{ substr($conv->platform, 0, 2) }}
                            </span>
                            <span class="text-sm font-semibold text-white truncate">
                                {{ $conv->buyer_name ?? 'Pembeli ' . $conv->buyer_id }}
                            </span>
                        </div>

                        {{-- Waktu --}}
                        <span class="text-xs text-gray-500 whitespace-nowrap flex-shrink-0">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true, true) : '' }}
                        </span>
                    </div>

                    {{-- Cuplikan Pesan Terakhir --}}
                    <p class="text-xs text-gray-400 truncate mb-1.5">
                        {{ $conv->last_message_preview ?: 'Belum ada pesan' }}
                    </p>

                    {{-- Badges Bawah (Toko & AI Status) --}}
                    <div class="flex items-center justify-between gap-1 text-xs">
                        <span class="text-gray-500 text-2xs truncate">
                            🏬 {{ $conv->store->shop_name ?? '-' }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            @if ($conv->unread_count > 0)
                                <span class="bg-indigo-600 text-white text-2xs px-1.5 py-0.2 rounded-full font-bold">
                                    {{ $conv->unread_count }}
                                </span>
                            @endif

                            @if ($conv->ai_enabled)
                                <span class="text-2xs bg-green-500/10 text-green-400 border border-green-500/20 px-1.5 py-0.5 rounded">
                                    🤖 AI
                                </span>
                            @else
                                <span class="text-2xs bg-amber-500/10 text-amber-400 border border-amber-500/20 px-1.5 py-0.5 rounded">
                                    👤 Takeover
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <p class="text-xs text-gray-500 mb-3">Belum ada percakapan masuk.</p>
                    <button type="button"
                            onclick="document.getElementById('simulatorModal').classList.remove('hidden')"
                            class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg transition">
                        + Tes Kirim Pesan
                    </button>
                </div>
            @endforelse
        </div>

    </div>

    {{-- ── KOLOM KANAN: Jendela Obrolan Aktif ─────────────────────── --}}
    <div class="flex-1 flex flex-col bg-gray-950/40">
        @if ($activeConversation)
            
            {{-- Header Chat --}}
            <div class="p-4 border-b border-gray-800 flex flex-wrap items-center justify-between gap-3 bg-gray-900/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold text-white shadow-inner">
                        {{ strtoupper(substr($activeConversation->buyer_name ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-semibold text-white">
                                {{ $activeConversation->buyer_name ?? 'Pembeli ' . $activeConversation->buyer_id }}
                            </h2>
                            <span class="text-xs px-2 py-0.5 rounded uppercase font-medium {{ $activeConversation->platform === 'shopee' ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : 'bg-gray-800 text-gray-300' }}">
                                {{ ucfirst($activeConversation->platform) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Toko: <span class="text-gray-300 font-medium">{{ $activeConversation->store->shop_name }}</span>
                        </p>
                    </div>
                </div>

                {{-- Kontrol Human Takeover --}}
                <div class="flex items-center gap-3">
                    <form action="{{ route('inbox.toggle-ai', $activeConversation) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-2 text-xs font-medium px-3.5 py-1.5 rounded-xl border transition {{ $activeConversation->ai_enabled ? 'bg-green-500/10 text-green-400 border-green-500/30 hover:bg-green-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/30 hover:bg-amber-500/20' }}">
                            @if ($activeConversation->ai_enabled)
                                <span>🤖 AI Auto-Reply (Aktif)</span>
                                <span class="text-2xs bg-green-500/20 px-1.5 py-0.5 rounded">Klik utk Takeover</span>
                            @else
                                <span>👤 Human Takeover (Aktif)</span>
                                <span class="text-2xs bg-amber-500/20 px-1.5 py-0.5 rounded">Klik aktifkan AI</span>
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            {{-- Banner Status AI / Human Takeover --}}
            <div class="px-4 py-2 text-xs flex items-center justify-between {{ $activeConversation->ai_enabled ? 'bg-indigo-950/40 border-b border-indigo-900/30 text-indigo-300' : 'bg-amber-950/30 border-b border-amber-900/30 text-amber-300' }}">
                <span>
                    @if ($activeConversation->ai_enabled)
                        ⚡ Pesan pembeli berikutnya akan otomatis dijawab oleh AI menggunakan basis pengetahuan tokomu.
                    @else
                        ✋ AI dinonaktifkan untuk chat ini. Kamu membalas secara manual sebagai Customer Service.
                    @endif
                </span>
            </div>

            {{-- Area Gelembung Pesan (Chat Thread) --}}
            <div class="flex-1 p-5 overflow-y-auto space-y-4">
                @foreach ($messages as $msg)
                    @if ($msg->direction === 'inbound')
                        {{-- Pesan Masuk dari Pembeli (Sebelah Kiri) --}}
                        <div class="flex items-start gap-2.5 max-w-lg">
                            <div class="w-7 h-7 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-xs font-semibold text-gray-300 flex-shrink-0 mt-0.5">
                                {{ strtoupper(substr($activeConversation->buyer_name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <div class="bg-gray-800 border border-gray-700/80 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-200 leading-relaxed shadow-sm">
                                    {{ $msg->content }}
                                </div>
                                <span class="text-2xs text-gray-500 mt-1 block">
                                    {{ $activeConversation->buyer_name }} • {{ $msg->created_at->format('H:i') }}
                                </span>
                            </div>
                        </div>

                    @else
                        {{-- Pesan Keluar (Sebelah Kanan: Dibalas AI atau Seller) --}}
                        <div class="flex items-start justify-end gap-2.5 max-w-lg ml-auto">
                            <div class="text-right">
                                <div class="rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm text-white leading-relaxed text-left shadow-sm {{ $msg->sender_type === 'ai' ? 'bg-indigo-600 border border-indigo-500/50' : 'bg-gray-700 border border-gray-600' }}">
                                    {{ $msg->content }}
                                </div>
                                <div class="flex items-center justify-end gap-2 text-2xs text-gray-400 mt-1">
                                    @if ($msg->sender_type === 'ai')
                                        <span class="inline-flex items-center gap-1 text-indigo-400 font-medium">
                                            🤖 AI Auto-Reply
                                            @if (!empty($msg->ai_context['provider']))
                                                ({{ strtoupper($msg->ai_context['provider']) }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-gray-300 font-medium">
                                            👤 Admin Seller
                                        </span>
                                    @endif
                                    <span>• {{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Form Input Balasan Manual --}}
            <div class="p-4 border-t border-gray-800 bg-gray-900/80">
                <form action="{{ route('inbox.reply', $activeConversation) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text"
                           name="content"
                           placeholder="Ketik balasan manual ke pembeli..."
                           class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500"
                           required
                           autocomplete="off">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition flex items-center gap-2">
                        <span>Kirim</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
            </div>

        @else
            {{-- State Kosong: Belum Memilih Chat --}}
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-800/80 text-gray-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-300 mb-1">Pilih Percakapan di Samping</h3>
                <p class="text-sm text-gray-500 max-w-sm mb-4">
                    Belum ada obrolan yang dipilih atau belum ada chat masuk dari marketplace.
                </p>
                <button type="button"
                        onclick="document.getElementById('simulatorModal').classList.remove('hidden')"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
                    ⚡ Coba Simulasi Pesan Masuk
                </button>
            </div>
        @endif
    </div>

</div>

{{-- ── MODAL: Simulator Chat Pembeli ─────────────────────────────── --}}
<div id="simulatorModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    ⚡
                </div>
                <div>
                    <h3 class="font-semibold text-white text-sm">Simulator Chat Marketplace</h3>
                    <p class="text-xs text-gray-400">Uji coba respons AI tanpa perlu API Shopee asli</p>
                </div>
            </div>
            <button type="button"
                    onclick="document.getElementById('simulatorModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-white text-lg">✕</button>
        </div>

        <form action="{{ route('inbox.simulate') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Pilihan Toko --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Kirim Ke Toko:</label>
                <select name="connected_store_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" required>
                    @foreach ($stores as $st)
                        <option value="{{ $st->id }}">
                            {{ $st->shop_name }} ({{ $st->platform_label }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Nama Pembeli --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Nama Pembeli (Simulasi):</label>
                <input type="text"
                       name="buyer_name"
                       value="Budi Santoso"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"
                       required>
            </div>

            {{-- Tombol Template Cepat --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Template Pertanyaan Cepat:</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button"
                            onclick="document.getElementById('simContent').value = 'Halo kak, barang ini apakah ready dan bisa kirim Sameday hari ini?'"
                            class="text-2xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 px-2 py-1 rounded-md transition">
                        📦 Tanya Pengiriman Sameday
                    </button>
                    <button type="button"
                            onclick="document.getElementById('simContent').value = 'Halo admin, apakah produk ini original dan ada garansi resmi?'"
                            class="text-2xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 px-2 py-1 rounded-md transition">
                        🛡️ Garansi & Keaslian
                    </button>
                    <button type="button"
                            onclick="document.getElementById('simContent').value = 'Kak kalau salah pilih varian apakah bisa tukar atau retur?'"
                            class="text-2xs bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 px-2 py-1 rounded-md transition">
                        🔄 Kebijakan Retur
                    </button>
                </div>
            </div>

            {{-- Isi Pesan --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Isi Pesan Pembeli:</label>
                <textarea id="simContent"
                          name="content"
                          rows="3"
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-indigo-500"
                          placeholder="Ketik pertanyaan pembeli..."
                          required>Halo kak, apakah barang ini ready dan bisa kirim hari ini?</textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button"
                        onclick="document.getElementById('simulatorModal').classList.add('hidden')"
                        class="px-4 py-2 text-xs rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-xs rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium transition shadow-lg shadow-indigo-600/20 flex items-center gap-1.5">
                    <span>Kirim & Lihat Balasan AI</span>
                    <span>→</span>
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
