<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchDataWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $webhookUrl, public string $table, public array $data, public string $tokenPrefix, public string $event = 'data.updated')
    {
        // data.created, data.updated, data.deleted
    }

    public function handle(): void
    {
        $payload = [
            'event' => $this->event,
            'table' => $this->table,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
            'source' => config('app.name'),
        ];

        // Sign payload dengan HMAC
        $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));

        try {
            $response = Http::withHeaders([
                'X-Filadelfia-Signature' => $signature,
                'X-Filadelfia-Token' => $this->tokenPrefix,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook failed', [
                    'url' => $this->webhookUrl,
                    'status' => $response->status(),
                    'table' => $this->table,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Webhook exception', [
                'error' => $e->getMessage(),
                'url' => $this->webhookUrl,
                'table' => $this->table,
            ]);
            // Queue akan retry otomatis
            throw $e;
        }
    }
}
