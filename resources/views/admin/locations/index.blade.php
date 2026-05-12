<x-layouts.admin>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Kelola Lokasi</h1>
            <p class="mt-1 text-sm text-gray-600">Upload Excel untuk menambah Lokasi, Zona, dan Lot.</p>
        </div>
    </div>

    @if ($latestImport)
        <div
            class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5"
            x-data="{
                importId: {{ (int) $latestImport->id }},
                status: '{{ $latestImport->status }}',
                total: {{ (int) $latestImport->total_rows }},
                processed: {{ (int) $latestImport->processed_rows }},
                createdLots: {{ (int) $latestImport->created_lots }},
                errorsCount: {{ (int) $latestImport->errors_count }},
                errors: @js($latestImport->errors ?? []),
                timer: null,
                visible: true,
                get statusLabel() {
                    const map = { queued: 'Menunggu', running: 'Memproses', completed: 'Selesai', failed: 'Gagal' };
                    return map[this.status] ?? this.status;
                },
                get percent() {
                    if (this.total <= 0) return 0;
                    return Math.min(100, Math.round((this.processed / this.total) * 100));
                },
                poll() {
                    fetch('{{ route('admin.locations.imports.status', $latestImport) }}', { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(d => {
                            this.status = d.status;
                            this.total = d.total_rows;
                            this.processed = d.processed_rows;
                            this.createdLots = d.created_lots;
                            this.errorsCount = d.errors_count;
                            this.errors = d.errors || [];
                            if (this.status === 'completed' || this.status === 'failed') {
                                clearInterval(this.timer);
                                this.timer = null;
                                setTimeout(() => { this.visible = false; }, 1500);
                            }
                        })
                        .catch(() => {});
                },
                init() {
                    if (this.status === 'queued' || this.status === 'running') {
                        this.timer = setInterval(() => this.poll(), 1000);
                    }
                }
            }"
        >
            <template x-if="visible">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Progress Import</h2>
                            <p class="mt-1 text-sm text-gray-700">
                                <span class="font-semibold" x-text="statusLabel"></span>
                                <span class="text-gray-400">â€¢</span>
                                <span x-text="total > 0 ? `${processed}/${total} baris` : `${processed} baris (menghitung...)`"></span>
                            </p>
                        </div>
                        <div class="text-sm text-gray-700" x-show="status === 'completed'">
                            Lot baru: <span class="font-semibold" x-text="createdLots"></span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-600 transition-all" :style="`width: ${percent}%`"></div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500" x-text="total > 0 ? `${percent}%` : 'â€”'"></div>
                    </div>
                </div>
            </template>

            {{-- Blocking modal while running/queued --}}
            <div x-show="visible && (status === 'queued' || status === 'running')" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-5 shadow-xl ring-1 ring-black/10">
                    <div class="text-base font-semibold text-gray-900">Sedang mengimpor</div>
                    <div class="mt-1 text-sm text-gray-700">
                        <span class="font-semibold" x-text="statusLabel"></span>
                        <span class="text-gray-400">â€¢</span>
                        <span x-text="total > 0 ? `${processed}/${total} baris` : `${processed} baris (menghitung...)`"></span>
                    </div>

                    <div class="mt-4">
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-600 transition-all" :style="`width: ${percent}%`"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">Jangan tutup halaman sampai selesai.</div>
                    </div>
                </div>
            </div>

            <template x-if="status === 'failed' && errors && errors.length">
                <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    <div class="font-semibold">Import gagal</div>
                    <ul class="mt-2 list-disc pl-5">
                        <template x-for="(err, idx) in errors" :key="idx">
                            <li>
                                Baris <span x-text="err.row ?? '-'"></span>: <span x-text="err.message"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    @endif

    <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <form action="{{ route('admin.locations.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 md:flex-row md:items-end">
            @csrf
            <div class="w-full">
                <label class="block text-sm font-semibold text-gray-900">Upload File Excel</label>
                <input name="file" type="file" accept=".xlsx,.xls" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                @error('file')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror
                <div class="mt-1 text-xs text-gray-500">Kolom A = Lokasi, B = Zona, C = Nomor Lot (baris pertama dianggap header).</div>
            </div>

            <div class="shrink-0">
                <x-button type="submit" color="primary">Upload & Preview</x-button>
            </div>
        </form>
    </div>

    @if ($importPreview)
        <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Preview Import</h2>
                    <div class="mt-1 text-sm text-gray-700">
                        Total baris terbaca: <span class="font-semibold">{{ $importPreview['rows_count'] ?? 0 }}</span>
                    </div>
                </div>

                <form id="confirm-import-form" action="{{ route('admin.locations.upload') }}" method="POST">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <x-button
                        type="button"
                        color="primary"
                        x-on:click="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Simpan Import', message: 'Data akan ditambahkan ke database. Lanjutkan?', confirmText: 'Simpan', confirmColor: 'bg-blue-700', formId: 'confirm-import-form' } }))"
                    >
                        Simpan
                    </x-button>
                </form>
            </div>

            @if (!empty($importPreview['errors']))
                <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    <div class="font-semibold">Ada error pada beberapa baris:</div>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($importPreview['errors'] as $err)
                            <li>Baris {{ $err['row'] ?? '-' }}: {{ $err['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!empty($importPreview['sample']))
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 text-gray-700">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Lokasi</th>
                                <th class="px-3 py-2 font-semibold">Zona</th>
                                <th class="px-3 py-2 font-semibold">Nomor Lot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($importPreview['sample'] as $r)
                                <tr>
                                    <td class="px-3 py-2">{{ $r['location'] }}</td>
                                    <td class="px-3 py-2">{{ $r['zone'] }}</td>
                                    <td class="px-3 py-2">{{ $r['lot_number'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2 text-xs text-gray-500">Menampilkan maksimal 25 baris untuk preview.</div>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-8 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <h2 class="text-base font-semibold text-gray-900">Data Lokasi</h2>

        @if ($locations->isEmpty())
            <div class="mt-3 text-sm text-gray-600">Belum ada data lokasi.</div>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($locations as $location)
                    <details class="rounded-md border border-gray-200 bg-white">
                        <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $location->name }}</div>
                            <div class="flex items-center gap-2">
                                <form id="delete-location-{{ $location->id }}" action="{{ route('admin.locations.destroy', $location) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button
                                        type="button"
                                        color="danger"
                                        x-on:click.stop="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Hapus Lokasi', message: 'Lokasi ini beserta zona dan lot terkait akan terhapus. Lanjutkan?', confirmText: 'Hapus', confirmColor: 'bg-red-600', formId: 'delete-location-{{ $location->id }}' } }))"
                                    >
                                        Hapus
                                    </x-button>
                                </form>
                            </div>
                        </summary>

                        <div class="border-t border-gray-200 px-4 py-3">
                            @forelse ($location->zones as $zone)
                                <div class="mb-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $zone->name }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @forelse ($zone->lots as $lot)
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-800">{{ $lot->number }}</span>
                                        @empty
                                            <span class="text-sm text-gray-500">Tidak ada lot.</span>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-600">Tidak ada zona.</div>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.admin>
