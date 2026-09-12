<?php

namespace App\Services;

use App\Models\ConnectedStore;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk memproses prompt dan menghasilkan balasan AI otomatis
 * berdasarkan basis pengetahuan (Knowledge Base) toko seller.
 */
class AiResponderService
{
    /**
     * Hasilkan balasan pesan otomatis untuk pembeli.
     *
     * @return array ['reply' => string, 'provider' => string, 'tokens' => int|null, 'context' => array]
     */
    public function generateReply(ConnectedStore $store, Conversation $conversation, string $incomingMessage): array
    {
        $seller = $store->user;

        // 1. Ambil Knowledge Base yang relevan (Global + Spesifik Toko Ini)
        $knowledgeItems = KnowledgeBase::where('user_id', $seller->id)
            ->where('is_active', true)
            ->where(function ($q) use ($store) {
                $q->whereNull('connected_store_id')
                  ->orWhere('connected_store_id', $store->id);
            })
            ->orderBy('priority', 'asc')
            ->get();

        // 2. Susun ringkasan Knowledge Base untuk disuntikkan ke System Prompt
        $knowledgeText = $this->formatKnowledgeBase($knowledgeItems);

        // 3. Susun System Prompt
        $systemPrompt = $this->buildSystemPrompt($store, $knowledgeText);

        // 4. Pilih Provider AI (Gemini atau OpenAI)
        $geminiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $openaiKey = config('services.openai.key', env('OPENAI_API_KEY'));
        $preferredProvider = env('AI_PROVIDER', 'gemini');

        // Coba panggil Gemini jika key ada
        if (($preferredProvider === 'gemini' || empty($openaiKey)) && !empty($geminiKey)) {
            try {
                $response = $this->callGemini($geminiKey, $systemPrompt, $incomingMessage);
                if ($response) {
                    return [
                        'reply'    => $response['text'],
                        'provider' => 'gemini',
                        'tokens'   => $response['tokens'] ?? null,
                        'context'  => ['knowledge_count' => $knowledgeItems->count()],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API Error, fallback ke rule matcher: ' . $e->getMessage());
            }
        }

        // Coba panggil OpenAI jika key ada
        if (!empty($openaiKey)) {
            try {
                $response = $this->callOpenAI($openaiKey, $systemPrompt, $incomingMessage);
                if ($response) {
                    return [
                        'reply'    => $response['text'],
                        'provider' => 'openai',
                        'tokens'   => $response['tokens'] ?? null,
                        'context'  => ['knowledge_count' => $knowledgeItems->count()],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI API Error, fallback ke rule matcher: ' . $e->getMessage());
            }
        }

        // 5. Fallback Cerdas jika belum isi API Key: Cocokkan kata kunci Knowledge Base
        return $this->smartFallbackReply($store, $incomingMessage, $knowledgeItems);
    }

    /**
     * Format daftar Knowledge Base menjadi format teks terstruktur.
     */
    protected function formatKnowledgeBase($items): string
    {
        if ($items->isEmpty()) {
            return "- Belum ada informasi spesifik. Balas dengan ramah dan minta pembeli menunggu seller.";
        }

        $lines = [];
        foreach ($items as $item) {
            $lines[] = "### [{$item->type_label}] {$item->title}\n{$item->content}";
        }

        return implode("\n\n", $lines);
    }

    /**
     * Susun instruksi persona dan batasan jawaban AI.
     */
    protected function buildSystemPrompt(ConnectedStore $store, string $knowledgeText): string
    {
        $platform = ucfirst($store->platform);

        return <<<PROMPT
Kamu adalah asisten AI toko online profesional dan ramah untuk toko "{$store->shop_name}" di platform {$platform}.

Pedoman Utama:
1. Balas dengan bahasa Indonesia yang ramah, sopan, natural dan solutif (khas admin online shop: sapa dengan "Halo kak", "Boleh dibantu kak", dll.).
2. Jawab secara padat, jelas, dan akurat berdasarkan informasi "BASIS PENGETAHUAN TOKO" di bawah ini.
3. Jangan membuat janji atau informasi palsu di luar data yang diberikan. Jika ada informasi yang tidak kamu ketahui, katakan dengan sopan bahwa kamu akan mengeceknya ke tim gudang/toko.
4. Jangan menyebutkan bahwa kamu membaca dari "database" atau "prompt". Berperilakulah seperti customer service toko sungguhan.

BASIS PENGETAHUAN TOKO:
{$knowledgeText}
PROMPT;
    }

    /**
     * Panggil Google Gemini API (gemini-1.5-flash / gemini-2.5).
     */
    protected function callGemini(string $apiKey, string $systemPrompt, string $userMessage): ?array
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $userMessage]]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 300,
            ],
        ];

        $response = Http::timeout(5)->post($endpoint, $payload);

        if ($response->successful()) {
            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $tokens = $json['usageMetadata']['totalTokenCount'] ?? null;

            if ($text) {
                return ['text' => trim($text), 'tokens' => $tokens];
            }
        }

        return null;
    }

    /**
     * Panggil OpenAI API (gpt-4o-mini).
     */
    protected function callOpenAI(string $apiKey, string $systemPrompt, string $userMessage): ?array
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.4,
            'max_tokens'  => 300,
        ];

        $response = Http::withToken($apiKey)->timeout(5)->post($endpoint, $payload);

        if ($response->successful()) {
            $json = $response->json();
            $text = $json['choices'][0]['message']['content'] ?? null;
            $tokens = $json['usage']['total_tokens'] ?? null;

            if ($text) {
                return ['text' => trim($text), 'tokens' => $tokens];
            }
        }

        return null;
    }

    /**
     * Fallback Cerdas jika belum memasukkan API Key:
     * Mencocokkan kata kunci pertanyaan pembeli dengan Knowledge Base toko.
     */
    protected function smartFallbackReply(ConnectedStore $store, string $incomingMessage, $knowledgeItems): array
    {
        $lowerMsg = strtolower($incomingMessage);
        $matchedItem = null;

        // Cari item Knowledge Base yang paling cocok
        foreach ($knowledgeItems as $item) {
            // Cek keywords jika ada
            if (!empty($item->keywords) && is_array($item->keywords)) {
                foreach ($item->keywords as $kw) {
                    if (str_contains($lowerMsg, strtolower(trim($kw)))) {
                        $matchedItem = $item;
                        break 2;
                    }
                }
            }

            // Cek kecocokan kata pada judul
            $titleWords = explode(' ', strtolower($item->title));
            foreach ($titleWords as $word) {
                if (strlen($word) >= 4 && str_contains($lowerMsg, $word)) {
                    $matchedItem = $item;
                    break 2;
                }
            }
        }

        if ($matchedItem) {
            $reply = "Halo kak! Mengenai pertanyaan tersebut, berikut informasi dari {$store->shop_name}:\n\n{$matchedItem->content}\n\nAda lagi yang bisa kami bantu kak? 😊";
        } else {
            $reply = "Halo kak, terima kasih sudah menghubungi {$store->shop_name}! Pesan kakak sudah kami terima. Boleh diinformasikan detail pesanan atau pertanyaan lebih lanjut agar bisa kami bantu dengan cepat ya kak! 🙏";
        }

        return [
            'reply'    => $reply,
            'provider' => 'simulator_rule_match',
            'tokens'   => null,
            'context'  => ['matched_kb_id' => $matchedItem?->id],
        ];
    }
}
