<?php

namespace App\Services;

use App\Models\ConnectedStore;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;

/**
 * Service untuk memproses arus pesan masuk (inbound) dan keluar (outbound)
 * dari marketplace serta mengorkestrasi balasan AI.
 */
class MarketplaceChatService
{
    public function __construct(
        protected AiResponderService $aiService
    ) {}

    /**
     * Tangkap pesan masuk dari pembeli (Webhook / Chat Simulator).
     *
     * @param ConnectedStore $store
     * @param array $payload [
     *    'platform_conversation_id' => string,
     *    'buyer_id'                 => string,
     *    'buyer_name'               => string,
     *    'content'                  => string,
     *    'platform_message_id'      => string|null,
     * ]
     * @return array ['conversation' => Conversation, 'inbound' => Message, 'reply' => Message|null]
     */
    public function handleIncomingMessage(ConnectedStore $store, array $payload): array
    {
        $conversationId = $payload['platform_conversation_id'] ?? 'conv_' . Str::random(8);
        $buyerId        = $payload['buyer_id'] ?? 'buyer_' . Str::random(6);
        $buyerName      = $payload['buyer_name'] ?? 'Pembeli';
        $content        = trim($payload['content']);

        // 1. Dapatkan atau buat Conversation (Chat Room)
        $conversation = Conversation::firstOrCreate(
            [
                'connected_store_id'       => $store->id,
                'platform_conversation_id' => $conversationId,
            ],
            [
                'platform'             => $store->platform,
                'buyer_id'             => $buyerId,
                'buyer_name'           => $buyerName,
                'ai_enabled'           => true, // Default AI aktif
                'status'               => 'open',
                'unread_count'         => 0,
            ]
        );

        // Update nama pembeli jika sebelumnya belum ada
        if (empty($conversation->buyer_name) && !empty($buyerName)) {
            $conversation->update(['buyer_name' => $buyerName]);
        }

        // 2. Simpan pesan masuk dari pembeli (Inbound)
        $inboundMessage = Message::create([
            'conversation_id'     => $conversation->id,
            'platform_message_id' => $payload['platform_message_id'] ?? 'msg_' . Str::random(10),
            'direction'           => 'inbound',
            'sender_type'         => 'buyer',
            'message_type'        => 'text',
            'content'             => $content,
            'status'              => 'sent',
            'sent_at'             => now(),
            'platform_created_at' => now(),
        ]);

        // 3. Update preview pesan terakhir pada percakapan
        $conversation->update([
            'last_message_preview' => Str::limit($content, 80),
            'last_message_at'      => now(),
            'unread_count'         => $conversation->unread_count + 1,
            'status'               => 'open',
        ]);

        $replyMessage = null;

        // 4. Periksa apakah AI Auto-Reply berhak membalas:
        // Syarat: Toko mengaktifkan AI DAN Percakapan tidak sedang dalam status Human Takeover
        if ($store->ai_enabled && $conversation->ai_enabled) {
            
            // Generate balasan via AI Service (Gemini / OpenAI / KB Matcher)
            $aiResult = $this->aiService->generateReply($store, $conversation, $content);

            // Simpan balasan AI (Outbound)
            $replyMessage = Message::create([
                'conversation_id'     => $conversation->id,
                'platform_message_id' => 'ai_rep_' . Str::random(10),
                'direction'           => 'outbound',
                'sender_type'         => 'ai',
                'message_type'        => 'text',
                'content'             => $aiResult['reply'],
                'status'              => 'sent',
                'ai_context'          => [
                    'provider' => $aiResult['provider'],
                    'context'  => $aiResult['context'] ?? null,
                ],
                'ai_tokens_used'      => $aiResult['tokens'] ?? null,
                'sent_at'             => now(),
            ]);

            // Update preview pesan terakhir dengan balasan AI
            $conversation->update([
                'last_message_preview' => '🤖 AI: ' . Str::limit($aiResult['reply'], 75),
                'last_message_at'      => now(),
                'unread_count'         => 0, // Karena sudah langsung dibalas AI
            ]);
        }

        return [
            'conversation' => $conversation,
            'inbound'      => $inboundMessage,
            'reply'        => $replyMessage,
        ];
    }

    /**
     * Kirim balasan manual dari Seller (Human Reply).
     */
    public function sendManualReply(Conversation $conversation, string $content): Message
    {
        $message = Message::create([
            'conversation_id'     => $conversation->id,
            'platform_message_id' => 'seller_rep_' . Str::random(10),
            'direction'           => 'outbound',
            'sender_type'         => 'seller',
            'message_type'        => 'text',
            'content'             => trim($content),
            'status'              => 'sent',
            'sent_at'             => now(),
        ]);

        $conversation->update([
            'last_message_preview' => '👤 Seller: ' . Str::limit($content, 75),
            'last_message_at'      => now(),
            'unread_count'         => 0,
        ]);

        return $message;
    }
}
