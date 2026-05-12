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

        $now = now()->format('H:i');
        $key = 'send_time_1';
        $time = (string) ($settings->{$key} ?? '');
        if ($time === '' || $time !== $now) {
            return;
        }

        $lockKey = 'discord_notif_sent:'.now()->format('Y-m-d');
        if (!Cache::add($lockKey, 1, now()->addDays(2))) {
            return;
        }

        dispatch(new SendDiscordNotificationJob($key));
    }
}
