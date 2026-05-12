<x-layouts.admin>
    <div>
        <h1 class="text-xl font-semibold">Settings Discord</h1>
        <p class="mt-1 text-sm text-gray-600">Atur webhook dan jadwal pengiriman laporan.</p>
    </div>

    <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <form id="discord-settings-form" action="{{ route('admin.settings.update') }}" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PATCH')

            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Discord Webhook URL</label>
                <input
                    name="webhook_url"
                    type="url"
                    value="{{ old('webhook_url', $settings->webhook_url) }}"
                    placeholder="https://discord.com/api/webhooks/..."
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                >
                @error('webhook_url') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900">Jam Kirim</label>
                <input
                    name="send_time_1"
                    type="time"
                    value="{{ old('send_time_1', substr((string) $settings->send_time_1, 0, 5)) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                    required
                >
                @error('send_time_1') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div></div>

            <div class="sm:col-span-2 rounded-md bg-gray-50 p-4 text-sm text-gray-800">
                <div class="font-semibold">Info</div>
                <div class="mt-2 space-y-1 text-gray-700">
                    <div><span class="font-semibold">Notifikasi:</span> dikirim 1x per hari, berisi data booking untuk ziarah besok (H+1).</div>
                </div>
            </div>

            <div class="sm:col-span-2 flex items-center justify-end">
                <x-button
                    type="button"
                    color="primary"
                    x-on:click="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Simpan Settings', message: 'Simpan perubahan settings Discord?', confirmText: 'Simpan', confirmColor: 'bg-blue-700', formId: 'discord-settings-form' } }))"
                >
                    Simpan
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
