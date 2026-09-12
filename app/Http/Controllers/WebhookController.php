<?php

namespace App\Http\Controllers;

use App\Models\ConnectedStore;
use App\Services\MarketplaceChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menangani Webhook Pesan Masuk dari Marketplace
 * (Shopee Open Platform, TikTok Shop API, Lazada Open Platform).
 */
class WebhookController extends Controller
{
    public function __construct(
        protected MarketplaceChatService $chatService
    ) {}

    /**
     * Endpoint terpadu dari Node.js Webhook Gateway (POST /api/incoming-chat).
     */
    public function handleIncomingChat(Request $request): JsonResponse
    {
        $platform = $request->input('platform', 'shopee');
        $data = $request->input('data', []);

        // Buat sub-request dengan payload marketplace
        $subRequest = Request::create('', 'POST', $data);

        return match ($platform) {
            'shopee' => $this->handleShopee($subRequest),
            'tiktok' => $this->handleTikTok($subRequest),
            'lazada' => $this->handleLazada($subRequest),
            default  => response()->json(['status' => 'error', 'message' => 'Platform tidak didukung'], 400),
        };
    }

    /**
     * Webhook Shopee Open Platform (Event: chat_message_received).
     * Endpoint: POST /api/webhook/shopee
     */
    public function handleShopee(Request $request): JsonResponse
    {
        Log::info('Shopee Webhook Received', $request->all());

        $shopId = $request->input('shop_id');
        $store = ConnectedStore::where('platform', 'shopee')
            ->where('shop_id', (string) $shopId)
            ->where('is_active', true)
            ->first();

        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found or inactive'], 404);
        }

        // Ekstrak data pesan sesuai schema Shopee Chat API
        $payload = [
            'platform_conversation_id' => (string) $request->input('data.conversation_id'),
            'buyer_id'                 => (string) $request->input('data.to_id'),
            'buyer_name'               => $request->input('data.buyer_username', 'Pembeli Shopee'),
            'content'                  => $request->input('data.content.text', ''),
            'platform_message_id'      => (string) $request->input('data.message_id'),
        ];

        if (!empty($payload['content'])) {
            $this->chatService->handleIncomingMessage($store, $payload);
        }

        return response()->json(['status' => 'success', 'message' => 'Shopee webhook processed']);
    }

    /**
     * Webhook TikTok Shop API.
     * Endpoint: POST /api/webhook/tiktok
     */
    public function handleTikTok(Request $request): JsonResponse
    {
        Log::info('TikTok Shop Webhook Received', $request->all());

        $shopId = $request->input('shop_id');
        $store = ConnectedStore::where('platform', 'tiktok')
            ->where('shop_id', (string) $shopId)
            ->where('is_active', true)
            ->first();

        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found'], 404);
        }

        $payload = [
            'platform_conversation_id' => (string) $request->input('data.conversation_id'),
            'buyer_id'                 => (string) $request->input('data.sender.id'),
            'buyer_name'               => $request->input('data.sender.name', 'Pembeli TikTok'),
            'content'                  => $request->input('data.content', ''),
            'platform_message_id'      => (string) $request->input('data.msg_id'),
        ];

        if (!empty($payload['content'])) {
            $this->chatService->handleIncomingMessage($store, $payload);
        }

        return response()->json(['status' => 'success', 'message' => 'TikTok webhook processed']);
    }

    /**
     * Webhook Lazada Open Platform.
     * Endpoint: POST /api/webhook/lazada
     */
    public function handleLazada(Request $request): JsonResponse
    {
        Log::info('Lazada Webhook Received', $request->all());

        $sellerId = $request->input('seller_id');
        $store = ConnectedStore::where('platform', 'lazada')
            ->where('shop_id', (string) $sellerId)
            ->where('is_active', true)
            ->first();

        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found'], 404);
        }

        $payload = [
            'platform_conversation_id' => (string) $request->input('session_id'),
            'buyer_id'                 => (string) $request->input('buyer_id'),
            'buyer_name'               => $request->input('buyer_name', 'Pembeli Lazada'),
            'content'                  => $request->input('message.text', ''),
            'platform_message_id'      => (string) $request->input('message_id'),
        ];

        if (!empty($payload['content'])) {
            $this->chatService->handleIncomingMessage($store, $payload);
        }

        return response()->json(['status' => 'success', 'message' => 'Lazada webhook processed']);
    }
}
