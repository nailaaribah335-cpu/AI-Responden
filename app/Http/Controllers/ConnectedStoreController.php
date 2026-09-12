<?php

namespace App\Http\Controllers;

use App\Models\ConnectedStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Mengelola toko marketplace yang dihubungkan oleh Seller.
 * CRUD: Lihat daftar, Tambah (simulasi OAuth), Hapus.
 *
 * Catatan: OAuth flow nyata (redirect ke Shopee/Lazada/TikTok)
 * akan diimplementasikan di Langkah berikutnya. Untuk sekarang,
 * Seller bisa input token secara manual (mode developer).
 */
class ConnectedStoreController extends Controller
{
    /** Halaman daftar semua toko yang sudah dihubungkan */
    public function index()
    {
        $stores = auth()->user()->stores()->latest()->get();

        return view('stores.index', compact('stores'));
    }

    /** Form tambah toko baru */
    public function create()
    {
        return view('stores.create');
    }

    /** Simpan toko baru ke database */
    public function store(Request $request)
    {
        $data = $request->validate([
            'platform'      => 'required|in:shopee,lazada,tiktok',
            'shop_id'       => 'required|string|max:100',
            'shop_name'     => 'required|string|max:150',
            'access_token'  => 'required|string',
            'refresh_token' => 'nullable|string',
        ]);

        // Cegah toko yang sama ditambahkan dua kali
        $alreadyExists = auth()->user()->stores()
            ->where('platform', $data['platform'])
            ->where('shop_id', $data['shop_id'])
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors(['shop_id' => 'Toko ini sudah dihubungkan sebelumnya.']);
        }

        // Enkripsi token sebelum disimpan ke database
        auth()->user()->stores()->create([
            ...$data,
            'access_token'   => Crypt::encryptString($data['access_token']),
            'refresh_token'  => $data['refresh_token'] ? Crypt::encryptString($data['refresh_token']) : null,
            'webhook_secret' => Str::random(32), // Generate secret untuk verifikasi webhook
        ]);

        return redirect()->route('stores.index')->with('success', 'Toko berhasil dihubungkan!');
    }

    /** Hapus / putuskan toko dari akun seller */
    public function destroy(ConnectedStore $store)
    {
        // Pastikan seller hanya bisa hapus toko miliknya sendiri
        $this->authorize('delete', $store);

        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Toko berhasil diputuskan.');
    }

    /** Toggle aktif/nonaktif AI untuk satu toko */
    public function toggleAi(ConnectedStore $store)
    {
        $this->authorize('update', $store);

        $store->update(['ai_enabled' => ! $store->ai_enabled]);

        $status = $store->ai_enabled ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "AI untuk {$store->shop_name} berhasil {$status}.");
    }
}
