<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\RestockNotification;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptScannerController extends Controller
{
    public function __construct(
        private ReceiptService $receiptService,
    ) {}

    // ─── Dashboard Utama Receipt Scanner ─────────────────────────────

    /**
     * Halaman utama: Form upload struk + form resi + tabel riwayat.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $orders = Order::where('user_id', $userId)
            ->with('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('nama_pelanggan', 'LIKE', "%{$request->search}%")
                        ->orWhere('id_transaksi', 'LIKE', "%{$request->search}%")
                        ->orWhere('nomor_resi', 'LIKE', "%{$request->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'     => Order::where('user_id', $userId)->count(),
            'pending'   => Order::where('user_id', $userId)->where('status', 'pending')->count(),
            'selesai'   => Order::where('user_id', $userId)->where('status', 'selesai')->count(),
            'stok_alert' => Inventory::where('user_id', $userId)
                ->whereColumn('stok', '<=', 'stok_minimum')->count(),
        ];

        $unreadAlerts = RestockNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->with('inventory')
            ->latest()
            ->take(10)
            ->get();

        return view('receipt-scanner.index', compact('orders', 'stats', 'unreadAlerts'));
    }

    // ─── Fitur 1: Upload & Scan Struk (OCR) ─────────────────────────

    /**
     * Menerima upload gambar struk → simulasi panggil OCR API → tampilkan form preview.
     */
    public function uploadStruk(Request $request)
    {
        $request->validate([
            'gambar_struk' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Simpan gambar
        $path = $request->file('gambar_struk')->store('struks', 'public');

        // ─── Simulasi Response OCR API ───────────────────────────
        // Di produksi, di sini Anda panggil API OCR pihak ke-3 (mis. Google Vision, Tesseract API)
        // dan terima response JSON-nya.
        //
        // Untuk demo, kita simulasi response:
        $ocrRawResponse = $this->simulateOcrApiCall($path);

        // Parse OCR response
        $parsed = $this->receiptService->parseOcrResponse($ocrRawResponse);

        // Ambil daftar inventaris user untuk dropdown
        $inventories = Inventory::where('user_id', Auth::id())
            ->orderBy('nama_produk')
            ->get();

        return view('receipt-scanner.preview-struk', [
            'data'           => $parsed['data'],
            'items'          => $parsed['items'],
            'gambar_struk'   => $path,
            'ocr_raw'        => $ocrRawResponse,
            'inventories'    => $inventories,
        ]);
    }

    /**
     * Simpan data struk yang sudah di-review admin.
     */
    public function storeStruk(Request $request)
    {
        $request->validate([
            'nama_pelanggan'     => 'required|string|max:255',
            'tanggal'            => 'required|date',
            'total_harga'        => 'required|numeric|min:0',
            'id_transaksi'       => 'nullable|string|max:100',
            'gambar_struk'       => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.nama_item'  => 'required|string|max:255',
            'items.*.jumlah'     => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        // ─── Validasi Anti Double-Input ──────────────────────────
        $duplicateMessage = $this->receiptService->checkDuplicate($userId, $request->all());
        if ($duplicateMessage) {
            return back()
                ->withInput()
                ->withErrors(['duplikasi' => $duplicateMessage]);
        }

        // ─── Simpan Order + Dispatch Job 5 Menit ────────────────
        $orderData = $request->only(['nama_pelanggan', 'tanggal', 'total_harga', 'id_transaksi', 'gambar_struk']);
        $orderData['ocr_raw_response'] = $request->input('ocr_raw');

        $order = $this->receiptService->createOrder(
            userId: $userId,
            data:   $orderData,
            items:  $request->input('items'),
            sumber: 'scan_struk',
        );

        return redirect()->route('receipt-scanner.index')
            ->with('success', "Struk berhasil diproses! Pesanan #{$order->id} berstatus 'Pending'. Status akan otomatis berubah ke 'Selesai' dalam 1 menit.");
    }

    // ─── Fitur 2: Input via Nomor Resi ───────────────────────────────

    /**
     * Form input resi.
     */
    public function formResi()
    {
        return view('receipt-scanner.form-resi');
    }

    /**
     * Cari data pesanan berdasarkan nomor resi.
     */
    public function cariResi(Request $request)
    {
        $request->validate([
            'nomor_resi' => 'required|string|max:100',
        ]);

        $userId = Auth::id();

        // Cari di tabel orders yang sudah ada
        $existingOrder = Order::where('user_id', $userId)
            ->where('nomor_resi', $request->nomor_resi)
            ->with('items')
            ->first();

        if ($existingOrder) {
            return view('receipt-scanner.hasil-resi', [
                'order'     => $existingOrder,
                'is_exists' => true,
                'message'   => "Data pesanan dengan resi '{$request->nomor_resi}' ditemukan.",
            ]);
        }

        // Jika tidak ditemukan, tampilkan form manual entry
        return view('receipt-scanner.hasil-resi', [
            'nomor_resi' => $request->nomor_resi,
            'is_exists'  => false,
            'message'    => "Resi '{$request->nomor_resi}' tidak ditemukan. Silakan input data manual.",
        ]);
    }

    /**
     * Simpan data dari input resi (entry baru).
     */
    public function storeResi(Request $request)
    {
        $request->validate([
            'nomor_resi'         => 'required|string|max:100',
            'nama_pelanggan'     => 'required|string|max:255',
            'tanggal'            => 'required|date',
            'total_harga'        => 'required|numeric|min:0',
            'id_transaksi'       => 'nullable|string|max:100',
            'items'              => 'required|array|min:1',
            'items.*.nama_item'  => 'required|string|max:255',
            'items.*.jumlah'     => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        // ─── Validasi Anti Double-Input ──────────────────────────
        $duplicateMessage = $this->receiptService->checkDuplicate($userId, $request->all());
        if ($duplicateMessage) {
            return back()
                ->withInput()
                ->withErrors(['duplikasi' => $duplicateMessage]);
        }

        // ─── Simpan Order + Dispatch Job 5 Menit ────────────────
        $order = $this->receiptService->createOrder(
            userId: $userId,
            data:   $request->only(['nama_pelanggan', 'tanggal', 'total_harga', 'id_transaksi', 'nomor_resi']),
            items:  $request->input('items'),
            sumber: 'resi',
        );

        return redirect()->route('receipt-scanner.index')
            ->with('success', "Pesanan via resi #{$order->id} berhasil disimpan! Status akan otomatis menjadi 'Selesai' dalam 1 menit.");
    }

    // ─── Fitur: Detail & Aksi Manual ─────────────────────────────────

    /**
     * Lihat detail pesanan.
     */
    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load('items.inventory', 'restockNotifications.inventory');

        return view('receipt-scanner.show', compact('order'));
    }

    /**
     * Batalkan pesanan pending (manual cancel).
     */
    public function cancel(Order $order)
    {
        $this->authorizeOrder($order);

        if (! $order->isPending()) {
            return back()->withErrors(['status' => 'Hanya pesanan berstatus Pending yang bisa dibatalkan.']);
        }

        $order->update(['status' => 'dibatalkan']);

        return back()->with('success', "Pesanan #{$order->id} berhasil dibatalkan.");
    }

    // ─── Fitur: Manajemen Inventaris ─────────────────────────────────

    /**
     * Halaman daftar inventaris / stok.
     */
    public function inventoryIndex()
    {
        $inventories = Inventory::where('user_id', Auth::id())
            ->orderBy('nama_produk')
            ->paginate(20);

        return view('receipt-scanner.inventory', compact('inventories'));
    }

    /**
     * Simpan produk inventaris baru.
     */
    public function inventoryStore(Request $request)
    {
        $request->validate([
            'sku'           => 'required|string|max:50|unique:inventories,sku',
            'nama_produk'   => 'required|string|max:255',
            'stok'          => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'harga_satuan'  => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:20',
        ]);

        Inventory::create([
            'user_id' => Auth::id(),
            ...$request->only(['sku', 'nama_produk', 'stok', 'stok_minimum', 'harga_satuan', 'satuan']),
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke inventaris!');
    }

    /**
     * Update stok produk.
     */
    public function inventoryUpdate(Request $request, Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'stok'          => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'harga_satuan'  => 'required|numeric|min:0',
        ]);

        $inventory->update($request->only(['stok', 'stok_minimum', 'harga_satuan']));

        return back()->with('success', "Stok '{$inventory->nama_produk}' berhasil diperbarui!");
    }

    /**
     * Hapus produk inventaris.
     */
    public function inventoryDestroy(Inventory $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403);
        }

        $inventory->delete();

        return back()->with('success', "Produk '{$inventory->nama_produk}' dihapus dari inventaris.");
    }

    // ─── Notifikasi ──────────────────────────────────────────────────

    /**
     * Tandai notifikasi restock sebagai sudah dibaca.
     */
    public function markNotificationRead(RestockNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    // ─── Private Helpers ─────────────────────────────────────────────

    /**
     * Otorisasi bahwa order milik user yang sedang login.
     */
    private function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }
    }

    /**
     * Simulasi panggilan OCR API (untuk demo).
     *
     * Di produksi, ganti ini dengan HTTP call ke Google Vision / Tesseract / dsb.
     */
    private function simulateOcrApiCall(string $imagePath): array
    {
        // Simulasi delay API
        // usleep(500000); // 0.5 detik

        Log::info("[ReceiptScanner] Simulasi OCR API untuk file: {$imagePath}");

        // Return simulasi data yang "ter-extract" dari struk
        return [
            'nama_pelanggan' => 'Pelanggan Demo',
            'tanggal'        => now()->toDateString(),
            'total_harga'    => 150000,
            'id_transaksi'   => 'INV-' . strtoupper(substr(md5(time()), 0, 8)),
            'items'          => [
                [
                    'nama_item'    => 'Produk A',
                    'jumlah'       => 2,
                    'harga_satuan' => 50000,
                ],
                [
                    'nama_item'    => 'Produk B',
                    'jumlah'       => 1,
                    'harga_satuan' => 50000,
                ],
            ],
        ];
    }
}
