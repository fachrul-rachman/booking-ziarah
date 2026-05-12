<?php

namespace App\Services;

use App\Models\DiscordSetting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DiscordService
{
    public function __construct(private readonly Client $client)
    {
    }

    public function send(string $message, ?string $filePath = null): void
    {
        $settings = DiscordSetting::query()->first();
        $webhookUrl = $settings?->webhook_url;

        if (!$webhookUrl) {
            Log::info('Discord webhook_url kosong, skip kirim notifikasi.');
            return;
        }

        try {
            $multipart = [
                [
                    'name' => 'payload_json',
                    'contents' => json_encode(['content' => $message], JSON_UNESCAPED_UNICODE),
                ],
            ];

            if ($filePath) {
                $multipart[] = [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => 'data_booking_ziarah.xlsx',
                ];
            }

            $this->client->post($webhookUrl, [
                'timeout' => 20,
                'multipart' => $multipart,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim Discord webhook.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
