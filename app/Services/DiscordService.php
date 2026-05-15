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

    /**
     * @param  array<int,array{path:string,filename:string}>  $attachments
     */
    public function sendWithAttachments(string $message, array $attachments, array $embeds = []): void
    {
        $settings = DiscordSetting::query()->first();
        $webhookUrl = $settings?->webhook_url;

        if (!$webhookUrl) {
            Log::info('Discord webhook_url kosong, skip kirim notifikasi.');
            return;
        }

        try {
            $payload = ['content' => $message];
            if (!empty($embeds)) {
                $payload['embeds'] = $embeds;
            }

            $multipart = [
                [
                    'name' => 'payload_json',
                    'contents' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ],
            ];

            $idx = 1;
            foreach ($attachments as $att) {
                if (empty($att['path']) || !is_string($att['path']) || !file_exists($att['path'])) {
                    continue;
                }

                $multipart[] = [
                    'name' => "file{$idx}",
                    'contents' => fopen($att['path'], 'r'),
                    'filename' => (string) ($att['filename'] ?? ("file{$idx}.xlsx")),
                ];

                $idx++;
                if ($idx > 10) {
                    break; // Discord webhook max attachments
                }
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

    public function send(string $message, ?string $filePath = null, array $embeds = []): void
    {
        $settings = DiscordSetting::query()->first();
        $webhookUrl = $settings?->webhook_url;

        if (!$webhookUrl) {
            Log::info('Discord webhook_url kosong, skip kirim notifikasi.');
            return;
        }

        try {
            $payload = ['content' => $message];
            if (!empty($embeds)) {
                $payload['embeds'] = $embeds;
            }

            $multipart = [
                [
                    'name' => 'payload_json',
                    'contents' => json_encode($payload, JSON_UNESCAPED_UNICODE),
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
