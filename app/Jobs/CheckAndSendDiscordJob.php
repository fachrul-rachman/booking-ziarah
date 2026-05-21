<?php

namespace App\Jobs;

use App\Models\DiscordSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckAndSendDiscordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $settings = DiscordSetting::query()->first();

        if (!$settings) {
            return;
        }

        $now = now()->format('H:i:s');

        foreach (['send_time_1', 'send_time_2'] as $key) {
            $time = (string) ($settings->{$key} ?? '');

            if ($time === '') {
                continue;
            }

            // Normalize jika suatu saat value dari DB berbentuk "11:52"
            // atau "11:52:00", tetap bisa dibandingkan sebagai "H:i:s".
            $normalizedTime = strlen($time) === 5 ? $time.':00' : $time;

            if ($normalizedTime !== $now) {
                continue;
            }

            // Lock dibuat per tanggal + per key,
            // supaya send_time_1 dan send_time_2 tetap bisa jalan di hari yang sama.
            $lockKey = 'discord_notif_sent:'.$key.':'.now()->format('Y-m-d');

            if (!Cache::add($lockKey, 1, now()->addDays(2))) {
                continue;
            }

            dispatch(new SendDiscordNotificationJob($key));
        }
    }
}