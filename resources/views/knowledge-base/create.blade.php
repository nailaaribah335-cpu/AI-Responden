@extends('layouts.app')

@section('title', 'Tambah Pengetahuan — Knowledge Base')
@section('page-title', 'Tambah Pengetahuan AI')
@section('page-subtitle', 'Masukkan informasi acuan agar AI bisa membalas chat pelanggan secara akurat')

@section('header-action')
    <a href="{{ route('knowledge-base.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white px-3 py-1.5 rounded-xl border border-gray-800 bg-gray-900 transition">
        ← Kembali
    </a>
@endsection

@section('content')

<div class="max-w-3xl">
    <form action="{{ route('knowledge-base.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-6">

            {{-- ── Tipe Pengetahuan & Target Toko ───────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Pilihan Tipe --}}
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                        Tipe Pengetahuan <span class="text-red-400">*</span>
                    </label>
                    <select id="type"
                            name="type"
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            required>
                        @foreach (\App\Models\KnowledgeBase::TYPES as $key => $info)
                            <option value="{{ $key }}" {{ old('type', 'faq') === $key ? 'selected' : '' }}>
                                {{ $info['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Target Toko --}}
                <div>
                    <label for="connected_store_id" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                        Berlaku Untuk Toko
                    </label>
                    <select id="connected_store_id"
                            name="connected_store_id"
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">🌐 Semua Toko (Global)</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ old('connected_store_id') == $store->id ? 'selected' : '' }}>
                                🏬 {{ $store->shop_name }} ({{ $store->platform_label }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih "Semua Toko" jika aturan ini berlaku umum.</p>
                </div>

            </div>

            {{-- ── Judul / Topik ────────────────────────────────────────── --}}
            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                    Judul / Topik Pertanyaan <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title') }}"
                       placeholder="Contoh: Jadwal Pengiriman & Ekspedisi atau Cara Klaim Garansi"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500"
                       required>
            </div>

            {{-- ── Isi Konten / Jawaban Acuan AI ───────────────────────── --}}
            <div>
                <label for="content" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                    Isi Informasi Acuan untuk AI <span class="text-red-400">*</span>
                </label>
                <textarea id="content"
                          name="content"
                          rows="6"
                          placeholder="Tuliskan jawaban atau petunjuk secara jelas. Contoh:
Pesanan masuk sebelum jam 15.00 WIB dikirim di hari yang sama.
Hari Minggu dan libur nasional tidak ada pengiriman.
Ekspedisi yang digunakan: J&T, SiCepat, dan Shopee Express."
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 leading-relaxed font-mono text-xs"
                          required>{{ old('content') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">AI akan menggunakan teks ini sebagai dasar jawaban saat membalas pertanyaan pembeli yang relevan.</p>
            </div>

            {{-- ── Kata Kunci (Keywords) & Prioritas ───────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                {{-- Kata Kunci --}}
                <div class="md:col-span-2">
                    <label for="keywords" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                        Kata Kunci Pencocokan (Keywords)
                    </label>
                    <input type="text"
                           id="keywords"
                           name="keywords"
                           value="{{ old('keywords') }}"
                           placeholder="ongkir, pengiriman, sameday, jnt, kurir"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Pisahkan tiap kata kunci dengan tanda koma (,).</p>
                </div>

                {{-- Prioritas --}}
                <div>
                    <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">
                        Tingkat Prioritas (1 - 10)
                    </label>
                    <select id="priority"
                            name="priority"
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="1" {{ old('priority', '5') == 1 ? 'selected' : '' }}>1 — Sangat Tinggi (Utama)</option>
                        <option value="3" {{ old('priority', '5') == 3 ? 'selected' : '' }}>3 — Tinggi</option>
                        <option value="5" {{ old('priority', '5') == 5 ? 'selected' : '' }}>5 — Normal (Standar)</option>
                        <option value="7" {{ old('priority', '5') == 7 ? 'selected' : '' }}>7 — Sedang</option>
                        <option value="10" {{ old('priority', '5') == 10 ? 'selected' : '' }}>10 — Pelengkap</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Prioritas lebih kecil diutamakan dibaca AI.</p>
                </div>

            </div>

        </div>

        {{-- ── Tombol Simpan & Batal ─────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition shadow-lg shadow-indigo-600/20">
                Simpan ke Otak AI
            </button>
            <a href="{{ route('knowledge-base.index') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-800 bg-gray-900 text-gray-400 hover:text-white text-sm transition">
                Batal
            </a>
        </div>

    </form>
</div>

@endsection
