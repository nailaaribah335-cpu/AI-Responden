<?php

namespace App\Http\Controllers;

use App\Models\ConnectedStore;
use App\Models\Conversation;
use App\Services\MarketplaceChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controller untuk Centralized Inbox (Pusat Kotak Masuk Pesan).
 * Seller dapat memantau percakapan dari semua toko marketplace,
 * mengambil alih chat manual (Human Takeover), dan menguji AI melalui Chat Simulator.
 */
class InboxController extends Controller
{
    public function __construct(
        protected MarketplaceChatService $chatService
    ) {}

    /**
     * Tampilan utama Centralized Inbox.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $storeIds = $user->stores()->pluck('id');

        // 1. Ambil daftar toko seller untuk dropdown filter & simulator
        $stores = $user->stores()->where('is_active', true)->get();

        // 2. Query daftar percakapan dari seluruh toko seller
        $query = Conversation::whereIn('connected_store_id', $storeIds)
            ->with(['store'])
            ->latest('last_message_at');

        // Filter toko spesifik
        if ($request->filled('store_id')) {
            $query->where('connected_store_id', $request->store_id);
        }

        // Filter platform (Shopee, Lazada, TikTok)
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter status AI / Human Takeover
        if ($request->filled('ai_status')) {
            if ($request->ai_status === 'ai_active') {
                $query->where('ai_enabled', true);
            } elseif ($request->ai_status === 'human_takeover') {
                $query->where('ai_enabled', false);
            }
        }

        // Pencarian nama pembeli atau isi pesan
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('buyer_name', 'LIKE', $search)
                  ->orWhere('last_message_preview', 'LIKE', $search);
            });
        }

        $conversations = $query->paginate(20)->withQueryString();

        // 3. Tentukan percakapan yang sedang aktif dibuka di panel kanan
        $activeConversation = null;
        $messages = collect();

        if ($request->filled('c')) {
            $activeConversation = Conversation::whereIn('connected_store_id', $storeIds)
                ->with(['store'])
                ->find($request->c);
        }

        // Jika tidak ada parameter 'c', default ke percakapan pertama
        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
        }

        // Load pesan-pesan percakapan aktif jika ada
        if ($activeConversation) {
            $messages = $activeConversation->messages()
                ->orderBy('created_at', 'asc')
                ->get();

            // Tandai sudah dibaca
            if ($activeConversation->unread_count > 0) {
                $activeConversation->update(['unread_count' => 0]);
            }
        }

        return view('inbox.index', compact(
            'conversations',
            'activeConversation',
            'messages',
            'stores'
        ));
    }

    /**
     * Balas pesan secara manual oleh Seller (Human Reply).
     */
    public function reply(Request $request, Conversation $conversation)
    {
        // Pastikan percakapan milik toko seller ini
        if ($conversation->store->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $this->chatService->sendManualReply($conversation, $request->content);

        return redirect()->route('inbox.index', ['c' => $conversation->id])
            ->with('success', 'Balasan manual berhasil terkirim!');
    }

    /**
     * Switch toggle Human Takeover (Aktifkan / Nonaktifkan AI pada chat ini).
     */
    public function toggleAi(Conversation $conversation)
    {
        if ($conversation->store->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $conversation->toggleAi(auth()->user());

        $statusMessage = $conversation->ai_enabled
            ? '🤖 AI Auto-Reply kembali diaktifkan untuk percakapan ini.'
            : '👤 Human Takeover aktif! AI dinonaktifkan sementara untuk percakapan ini.';

        return redirect()->route('inbox.index', ['c' => $conversation->id])
            ->with('success', $statusMessage);
    }

    /**
     * Simulator Chat Pembeli:
     * Fitur pengujian untuk mensimulasikan pembeli mengirim pesan dari Shopee/Lazada/TikTok,
     * sehingga Seller bisa langsung melihat AI merespons secara otomatis di bawah 3 detik.
     */
    public function simulateMessage(Request $request)
    {
        $validated = $request->validate([
            'connected_store_id' => 'required|exists:connected_stores,id',
            'buyer_name'         => 'required|string|max:100',
            'content'            => 'required|string|max:1000',
        ]);

        // Pastikan toko milik seller yang sedang login
        $store = auth()->user()->stores()->findOrFail($validated['connected_store_id']);

        // Generate ID percakapan platform unik untuk simulasi ini (atau gunakan yang sedang aktif)
        $platformConvId = $request->input('platform_conversation_id') ?: ('sim_conv_' . Str::slug($validated['buyer_name']) . '_' . substr(md5(now()), 0, 4));

        $result = $this->chatService->handleIncomingMessage($store, [
            'platform_conversation_id' => $platformConvId,
            'buyer_id'                 => 'sim_buyer_' . Str::slug($validated['buyer_name']),
            'buyer_name'               => $validated['buyer_name'],
            'content'                  => $validated['content'],
            'platform_message_id'      => 'sim_msg_' . Str::random(8),
        ]);

        $status = $result['reply']
            ? 'Pesan pembeli masuk dan otomatis dibalas oleh AI!'
            : 'Pesan pembeli masuk (AI tidak membalas karena Human Takeover sedang aktif).';

        return redirect()->route('inbox.index', ['c' => $result['conversation']->id])
            ->with('success', $status);
    }
}
