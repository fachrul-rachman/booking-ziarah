<x-layouts.admin>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-lg font-medium text-gray-900">Kelola Lokasi</h1>
            <p class="mt-0.5 text-xs text-gray-400">Upload Excel untuk menambah Lokasi, Zona, dan Lot.</p>
        </div>
    </div>

    {{-- Progress Import --}}
    @if ($latestImport)
        <div
            class="mt-5 rounded-xl bg-white border border-gray-100 p-5"
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
                            this.status = d.status; this.total = d.total_rows;
                            this.processed = d.processed_rows; this.createdLots = d.created_lots;
                            this.errorsCount = d.errors_count; this.errors = d.errors || [];
                            if (this.status === 'completed' || this.status === 'failed') {
                                clearInterval(this.timer); this.timer = null;
                                setTimeout(() => { this.visible = false; }, 1500);
                            }
                        }).catch(() => {});
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
                            <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400">Progress Import</div>
                            <p class="mt-1 text-sm text-gray-700">
                                <span class="font-medium" x-text="statusLabel"></span>
                                <span class="text-gray-300 mx-1">&middot;</span>
                                <span class="text-gray-500" x-text="total > 0 ? `${processed}/${total} baris` : `${processed} baris (menghitung...)`"></span>
                            </p>
                        </div>
                        <div class="text-sm text-gray-500" x-show="status === 'completed'">
                            Lot baru: <span class="font-medium text-gray-900" x-text="createdLots"></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gray-700 transition-all" :style="`width: ${percent}%`"></div>
                        </div>
                        <div class="mt-1 text-xs text-gray-400" x-text="total > 0 ? `${percent}%` : '-'"></div>
                    </div>
                </div>
            </template>

            <div x-show="visible && (status === 'queued' || status === 'running')" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 ring-1 ring-black/10">
                    <div class="text-sm font-medium text-gray-900">Sedang mengimpor</div>
                    <div class="mt-1 text-sm text-gray-500">
                        <span x-text="statusLabel"></span>
                        <span class="text-gray-300 mx-1">&middot;</span>
                        <span x-text="total > 0 ? `${processed}/${total} baris` : `${processed} baris (menghitung...)`"></span>
                    </div>
                    <div class="mt-4">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gray-700 transition-all" :style="`width: ${percent}%`"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-400">Jangan tutup halaman sampai selesai.</div>
                    </div>
                </div>
            </div>

            <template x-if="status === 'failed' && errors && errors.length">
                <div class="mt-4 rounded-lg bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                    <div class="font-medium">Import gagal</div>
                    <ul class="mt-2 list-disc pl-5 space-y-0.5 text-xs">
                        <template x-for="(err, idx) in errors" :key="idx">
                            <li>Baris <span x-text="err.row ?? '-'"></span>: <span x-text="err.message"></span></li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    @endif

    {{-- Upload --}}
    <div class="mt-4 rounded-xl bg-white border border-gray-100 p-5">
        <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-4 pb-3 border-b border-gray-100">
            Upload file excel
        </div>
        <form action="{{ route('admin.locations.upload') }}" method="POST" enctype="multipart/form-data"
              class="flex flex-col gap-3 md:flex-row md:items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">File</label>
                <input name="file" type="file" accept=".xlsx,.xls" required
                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 text-sm px-3 py-2 focus:border-gray-400 focus:ring-0">
                @error('file')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                @enderror
                <div class="mt-1.5 text-xs text-gray-400">Kolom A = Lokasi, B = Zona, C = Nomor Lot (baris pertama dianggap header).</div>
            </div>
            <div class="shrink-0">
                <x-button type="submit" color="primary"
                    class="h-9 px-5 bg-gray-800 hover:bg-gray-700 border-gray-800 text-sm font-medium whitespace-nowrap">
                    Upload &amp; Preview
                </x-button>
            </div>
        </form>
    </div>

    {{-- Preview (kondisional) --}}
    @if ($importPreview)
        <div class="mt-4 rounded-xl bg-white border border-gray-100 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1">Preview Import</div>
                    <div class="text-sm text-gray-500">
                        Total baris terbaca: <span class="font-medium text-gray-900">{{ $importPreview['rows_count'] ?? 0 }}</span>
                    </div>
                </div>
                <form id="confirm-import-form" action="{{ route('admin.locations.upload') }}" method="POST">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <x-button
                        type="button" color="primary"
                        class="h-9 px-5 bg-gray-800 hover:bg-gray-700 border-gray-800 text-sm font-medium"
                        x-on:click="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Simpan Import', message: 'Data akan ditambahkan ke database. Lanjutkan?', confirmText: 'Simpan', confirmColor: 'bg-gray-800', formId: 'confirm-import-form' } }))"
                    >Simpan</x-button>
                </form>
            </div>

            @if (!empty($importPreview['errors']))
                <div class="mt-4 rounded-lg bg-red-50 border border-red-100 p-4 text-sm">
                    <div class="font-medium text-red-800">Ada error pada beberapa baris:</div>
                    <ul class="mt-2 list-disc pl-5 text-xs text-red-700 space-y-0.5">
                        @foreach ($importPreview['errors'] as $err)
                            <li>Baris {{ $err['row'] ?? '-' }}: {{ $err['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!empty($importPreview['sample']))
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-4 py-2.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">Lokasi</th>
                                <th class="px-4 py-2.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">Zona</th>
                                <th class="px-4 py-2.5 text-[10px] font-medium uppercase tracking-widest text-gray-400">Nomor Lot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($importPreview['sample'] as $r)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700">{{ $r['location'] }}</td>
                                    <td class="px-4 py-2.5 text-gray-700">{{ $r['zone'] }}</td>
                                    <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $r['lot_number'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-4 py-2 text-xs text-gray-400 border-t border-gray-100">Menampilkan maksimal 25 baris untuk preview.</div>
                </div>
            @endif
        </div>
    @endif

    {{-- Data Lokasi --}}
    <div class="mt-4 rounded-xl bg-white border border-gray-100 p-5">
        <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-4 pb-3 border-b border-gray-100">
            Data lokasi
        </div>

        @if ($locations->isEmpty())
            <div class="text-sm text-gray-400">Belum ada data lokasi.</div>
        @else
            <div class="space-y-2">
                @foreach ($locations as $location)
                    @php
                        $totalLots = $location->zones->sum(fn($z) => $z->lots->count());
                        $zoneCount = $location->zones->count();
                    @endphp
                    <div x-data="{ open: false }" class="rounded-xl border border-gray-200 overflow-hidden">

                        {{-- Location header --}}
                        <div @click="open = !open"
                             class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors select-none">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $location->name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $zoneCount }} zona &middot; {{ number_format($totalLots) }} lot</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form id="delete-location-{{ $location->id }}"
                                      action="{{ route('admin.locations.destroy', $location) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button
                                        type="button" color="danger"
                                        class="h-7 px-3 bg-red-50 text-red-800 border border-red-200 hover:bg-red-100 text-xs font-medium"
                                        x-on:click.stop="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Hapus Lokasi', message: 'Lokasi ini beserta zona dan lot terkait akan terhapus. Lanjutkan?', confirmText: 'Hapus', confirmColor: 'bg-red-600', formId: 'delete-location-{{ $location->id }}' } }))"
                                    >Hapus</x-button>
                                </form>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                     :class="open ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Zones --}}
                        <div x-show="open" x-cloak
                             class="border-t border-gray-200 divide-y divide-gray-200">
                            @forelse ($location->zones as $zone)
                                @php $lotCount = $zone->lots->count(); @endphp
                                <div x-data="{ showLots: false }"
                                     class="px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-800">{{ $zone->name }}</span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded-full px-2.5 py-0.5">
                                                {{ number_format($lotCount) }} lot
                                            </span>
                                            @if ($lotCount > 0)
                                                <button @click="showLots = !showLots"
                                                    class="text-xs text-gray-400 underline underline-offset-2 hover:text-gray-700 transition-colors">
                                                    <span x-text="showLots ? 'Sembunyikan' : 'Lihat lot'"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Lots panel --}}
                                    <div x-show="showLots" x-cloak
                                         class="mt-2.5 max-h-36 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($zone->lots->take(100) as $lot)
                                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-2.5 py-0.5 font-mono text-xs text-gray-600">
                                                    {{ $lot->number }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if ($lotCount > 100)
                                            <div class="mt-2 text-[10px] text-gray-400">
                                                Menampilkan 100 dari {{ number_format($lotCount) }} lot.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-sm text-gray-400">Tidak ada zona.</div>
                            @endforelse
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.admin>