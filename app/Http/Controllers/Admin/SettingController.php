<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = DiscordSetting::query()->firstOrCreate([]);

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'webhook_url' => ['nullable', 'url', 'max:2048'],
            'send_time_1' => ['required', 'date_format:H:i'],
        ]);

        $settings = DiscordSetting::query()->firstOrCreate([]);
        $settings->webhook_url = $validated['webhook_url'];
        $settings->send_time_1 = $validated['send_time_1'];
        $settings->updated_by = $request->user()?->id;
        $settings->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Settings Discord berhasil disimpan.',
        ]);
    }
}
