<x-layouts.admin>
    <div>
        <h1 class="text-xl font-semibold">Time Slots</h1>
        <p class="mt-1 text-sm text-gray-600">Slot jam bersifat global untuk semua lokasi.</p>
    </div>

    <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <h2 class="text-base font-semibold text-gray-900">Generate Slot</h2>

        <form action="{{ route('admin.time-slots.generate') }}" method="POST" class="mt-3">
            @csrf
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:max-w-xs">
                    <label class="block text-sm font-semibold text-gray-900">Jam Mulai</label>
                    <input name="range_start" type="time" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    @error('range_start')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="w-full sm:max-w-xs">
                    <label class="block text-sm font-semibold text-gray-900">Jam Selesai</label>
                    <input name="range_end" type="time" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    @error('range_end')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="shrink-0">
                    <x-button type="submit" color="primary">Generate</x-button>
                </div>
            </div>

            <div class="mt-2 text-xs text-gray-500">
                Slot dibuat per 1 jam (jam selesai sebagai batas akhir); jika jam sama dengan jam mulai, sistem buat 24 jam.
            </div>
        </form>
    </div>

    <div class="mt-8 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <h2 class="text-base font-semibold text-gray-900">Daftar Slot</h2>

        @if ($timeSlots->isEmpty())
            <div class="mt-3 text-sm text-gray-600">Belum ada time slot.</div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($timeSlots as $slot)
                    <div class="rounded-md border border-gray-200 bg-white p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ substr((string) $slot->start_time, 0, 5) }} - {{ substr((string) $slot->end_time, 0, 5) }}
                                </div>
                                <div class="mt-1 text-sm">
                                    @if ($slot->is_active)
                                        <span class="inline-flex items-center rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-semibold text-white">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-300 px-2.5 py-0.5 text-xs font-semibold text-gray-900">Nonaktif</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('admin.time-slots.update', $slot) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $slot->is_active ? 0 : 1 }}">
                                    <x-button type="submit" color="secondary">
                                        {{ $slot->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </x-button>
                                </form>

                                <form id="delete-time-slot-{{ $slot->id }}" action="{{ route('admin.time-slots.destroy', $slot) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button
                                        type="button"
                                        color="danger"
                                        x-on:click="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Hapus Time Slot', message: 'Time slot ini akan dihapus. Lanjutkan?', confirmText: 'Hapus', confirmColor: 'bg-red-600', formId: 'delete-time-slot-{{ $slot->id }}' } }))"
                                    >
                                        Hapus
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.admin>
