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

        // Compare sampai menit saja, karena scheduler sering jalan di detik :01, :02, :03.
        $now = now()->format('H:i');

        foreach (['send_time_1', 'send_time_2'] as $key) {
            $time = (string) ($settings->{$key} ?? '');

            if ($time === '') {
                continue;
            }

            // DB bisa menyimpan "11:52:00", sedangkan $now adalah "11:52".
            $scheduledTime = substr($time, 0, 5);

            if ($scheduledTime !== $now) {
                continue;
            }

            // Lock per tanggal + per key, supaya send_time_1 dan send_time_2
            // tetap bisa terkirim di hari yang sama.
            $lockKey = 'discord_notif_sent:'.$key.':'.now()->format('Y-m-d');

            if (!Cache::add($lockKey, 1, now()->addDays(2))) {
                continue;
            }

            dispatch(new SendDiscordNotificationJob($key));
        }
    }
}