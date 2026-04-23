<?php

namespace App\Services;

use App\Models\ApiWebhook;
use App\Models\Result;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebhookDispatchService
{
    public function dispatchResultUpdated(Result $result): void
    {
        $webhooks = ApiWebhook::where('event', 'result.updated')
            ->where('is_active', true)
            ->get();

        $payload = [
            'event' => 'result.updated',
            'result' => [
                'id' => $result->id,
                'date' => $result->date?->format('Y-m-d'),
                'region' => $result->region,
                'province_code' => $result->province_code,
                'raw_data' => $result->raw_data,
            ],
            'sent_at' => now()->toISOString(),
        ];

        foreach ($webhooks as $webhook) {
            try {
                $response = Http::timeout(8)->post($webhook->url, $payload);
                $webhook->update([
                    'last_triggered_at' => now(),
                    'last_status_code' => $response->status(),
                    'last_response' => mb_substr($response->body(), 0, 1500),
                ]);
            } catch (Throwable $e) {
                $webhook->update([
                    'last_triggered_at' => now(),
                    'last_status_code' => 0,
                    'last_response' => mb_substr($e->getMessage(), 0, 1500),
                ]);
            }
        }
    }
}
