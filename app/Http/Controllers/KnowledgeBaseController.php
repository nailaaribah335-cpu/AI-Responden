<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola Knowledge Base (Otak AI).
 * Seller dapat mencatat FAQ, aturan kirim, promo, dan info produk
 * yang otomatis disuntikkan ke AI saat merespons chat marketplace.
 */
class KnowledgeBaseController extends Controller
{
    /**
     * Tampilkan daftar data pengetahuan seller dengan opsi filter tipe.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Query dasar: hanya ambil data milik seller yang sedang login
        $query = $user->knowledgeBases()->with('store');

        // Filter tipe jika dipilih oleh seller
        if ($request->filled('type') && in_array($request->type, array_keys(KnowledgeBase::TYPES))) {
            $query->where('type', $request->type);
        }

        // Pencarian berdasarkan judul atau isi konten
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                  ->orWhere('content', 'LIKE', $search);
            });
        }

        // Urutkan berdasarkan prioritas (1 paling penting) lalu waktu dibuat terbaru
        $items = $query->orderBy('priority', 'asc')
                       ->latest()
                       ->paginate(10)
                       ->withQueryString();

        return view('knowledge-base.index', compact('items'));
    }

    /**
     * Form tambah pengetahuan baru.
     */
    public function create()
    {
        // Ambil daftar toko seller untuk pilihan target toko
        $stores = auth()->user()->stores()->where('is_active', true)->get();

        return view('knowledge-base.create', compact('stores'));
    }

    /**
     * Simpan data pengetahuan baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'connected_store_id' => 'nullable|exists:connected_stores,id',
            'type'               => 'required|in:' . implode(',', array_keys(KnowledgeBase::TYPES)),
            'title'              => 'required|string|max:255',
            'content'            => 'required|string|max:5000',
            'keywords'           => 'nullable|string|max:500',
            'priority'           => 'required|integer|min:1|max:10',
        ]);

        // Verifikasi kepemilikan toko jika seller memilih toko tertentu
        if (!empty($validated['connected_store_id'])) {
            $storeBelongsToUser = auth()->user()->stores()
                ->where('id', $validated['connected_store_id'])
                ->exists();

            if (!$storeBelongsToUser) {
                return back()->withErrors(['connected_store_id' => 'Toko tidak valid.'])->withInput();
            }
        }

        // Ubah input string kata kunci koma menjadi array bersih (misal: "ongkir, jne, jnt")
        $keywordsArray = null;
        if (!empty($validated['keywords'])) {
            $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))));
        }

        // Simpan data
        auth()->user()->knowledgeBases()->create([
            'connected_store_id' => $validated['connected_store_id'] ?: null,
            'type'               => $validated['type'],
            'title'              => $validated['title'],
            'content'            => $validated['content'],
            'keywords'           => $keywordsArray,
            'priority'           => (int) $validated['priority'],
            'is_active'          => true,
        ]);

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Pengetahuan baru berhasil ditambahkan ke otak AI!');
    }

    /**
     * Form edit data pengetahuan.
     */
    public function edit(KnowledgeBase $knowledgeBase)
    {
        // Pastikan hanya pemilik yang bisa mengedit
        if ($knowledgeBase->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $stores = auth()->user()->stores()->where('is_active', true)->get();

        return view('knowledge-base.edit', compact('knowledgeBase', 'stores'));
    }

    /**
     * Simpan perubahan data pengetahuan.
     */
    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        if ($knowledgeBase->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'connected_store_id' => 'nullable|exists:connected_stores,id',
            'type'               => 'required|in:' . implode(',', array_keys(KnowledgeBase::TYPES)),
            'title'              => 'required|string|max:255',
            'content'            => 'required|string|max:5000',
            'keywords'           => 'nullable|string|max:500',
            'priority'           => 'required|integer|min:1|max:10',
            'is_active'          => 'sometimes|boolean',
        ]);

        // Verifikasi kepemilikan toko jika seller memilih toko tertentu
        if (!empty($validated['connected_store_id'])) {
            $storeBelongsToUser = auth()->user()->stores()
                ->where('id', $validated['connected_store_id'])
                ->exists();

            if (!$storeBelongsToUser) {
                return back()->withErrors(['connected_store_id' => 'Toko tidak valid.'])->withInput();
            }
        }

        // Parsing kata kunci
        $keywordsArray = null;
        if (!empty($validated['keywords'])) {
            $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $validated['keywords']))));
        }

        $knowledgeBase->update([
            'connected_store_id' => $validated['connected_store_id'] ?: null,
            'type'               => $validated['type'],
            'title'              => $validated['title'],
            'content'            => $validated['content'],
            'keywords'           => $keywordsArray,
            'priority'           => (int) $validated['priority'],
            'is_active'          => $request->has('is_active'),
        ]);

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Data pengetahuan berhasil diperbarui!');
    }

    /**
     * Hapus data pengetahuan (soft delete).
     */
    public function destroy(KnowledgeBase $knowledgeBase)
    {
        if ($knowledgeBase->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $knowledgeBase->delete();

        return redirect()->route('knowledge-base.index')
            ->with('success', 'Pengetahuan berhasil dihapus dari otak AI.');
    }

    /**
     * Cepat nyalakan / matikan status aktif pengetahuan.
     */
    public function toggleStatus(KnowledgeBase $knowledgeBase)
    {
        if ($knowledgeBase->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $knowledgeBase->update([
            'is_active' => !$knowledgeBase->is_active,
        ]);

        $statusText = $knowledgeBase->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pengetahuan '{$knowledgeBase->title}' berhasil {$statusText}.");
    }
}
