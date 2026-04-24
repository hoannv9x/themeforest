<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MiniGameAiSuggestionService
{
    public function suggest(array $topNumbers): array
    {
        $provider = config('services.mini_game_ai.provider', 'local');

        return match ($provider) {
            'gemini' => $this->suggestWithGemini($topNumbers),
            'openai' => $this->suggestWithOpenAi($topNumbers),
            default => $this->localSuggestion($topNumbers),
        };
    }

    private function suggestWithGemini(array $topNumbers): array
    {
        $apiKey = config('services.mini_game_ai.gemini.api_key');
        $model = config('services.mini_game_ai.gemini.model', 'gemini-1.5-flash');
        if (!$apiKey) {
            throw new \RuntimeException('Missing GEMINI_API_KEY');
        }

        $prompt = $this->buildPrompt($topNumbers);
        $response = Http::timeout(15)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [
                'contents' => [[
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini request failed: ' . $response->status());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        return $this->parseAiPayload($text, 'gemini');
    }

    private function suggestWithOpenAi(array $topNumbers): array
    {
        $apiKey = config('services.mini_game_ai.openai.api_key');
        $model = config('services.mini_game_ai.openai.model', 'gpt-4o-mini');
        if (!$apiKey) {
            throw new \RuntimeException('Missing OPENAI_API_KEY');
        }

        $prompt = $this->buildPrompt($topNumbers);
        $response = Http::timeout(15)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a lottery analyst assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI request failed: ' . $response->status());
        }

        $text = data_get($response->json(), 'choices.0.message.content', '');
        return $this->parseAiPayload($text, 'openai');
    }

    private function buildPrompt(array $topNumbers): string
    {
        $context = collect($topNumbers)->take(10)->values()->all();
        return "Du lieu top numbers (JSON): " . json_encode($context) . "\n" .
            "Hay de xuat 4 so hai chu so cho mini game, uu tien can bang rui ro.\n" .
            "Tra ve JSON object co dung schema:\n" .
            "{\"numbers\":[\"00\",\"11\",\"22\",\"33\"],\"message\":\"...\"}\n" .
            "Chi tra ve JSON, khong them text khac.";
    }

    private function parseAiPayload(string $content, string $provider): array
    {
        $decoded = json_decode(trim($content), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid AI payload');
        }

        $numbers = collect($decoded['numbers'] ?? [])
            ->map(function ($number) {
                $digits = preg_replace('/\D/', '', (string) $number);
                if ($digits === '') {
                    return null;
                }
                return str_pad(substr($digits, -2), 2, '0', STR_PAD_LEFT);
            })
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->all();

        if (count($numbers) === 0) {
            throw new \RuntimeException('AI returned empty numbers');
        }

        return [
            'provider' => $provider,
            'numbers' => $numbers,
            'message' => (string) ($decoded['message'] ?? 'Goi y duoc tao boi AI.'),
        ];
    }

    public function localSuggestion(array $topNumbers): array
    {
        $topFour = collect($topNumbers)->take(4)->pluck('number')->values()->all();
        return [
            'provider' => 'local-rule-based',
            'numbers' => $topFour,
            'message' => 'Goi y fallback noi bo tu xu huong binh chon.',
        ];
    }
}
