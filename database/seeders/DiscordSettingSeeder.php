<?php

namespace Database\Seeders;

use App\Models\DiscordSetting;
use Illuminate\Database\Seeder;

class DiscordSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscordSetting::query()->firstOrCreate(
            [],
            [
                'webhook_url' => null,
                'send_time_1' => '08:00',
                'send_time_2' => '14:00',
                'updated_by' => null,
            ],
        );
    }
}

