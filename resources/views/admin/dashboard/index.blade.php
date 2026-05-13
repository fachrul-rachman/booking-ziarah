<x-layouts.admin>
    <div>
        <h1 class="text-lg font-medium text-gray-900">Dashboard</h1>
        <p class="mt-0.5 text-xs text-gray-400">Daftar booking terbaru.</p>
    </div>

    {{-- Filter --}}
    <div class="mt-5 rounded-xl bg-white border border-gray-100 p-5">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-6 gap-3">

            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Tanggal dari</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0">
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Tanggal sampai</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0">
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Status</label>
                <select name="status"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0">
                    <option value="">Semua</option>
                    <option value="confirmed" @selected($filters['status'] === 'confirmed')>Confirmed</option>
                    <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Lokasi</label>
                <select name="location_id"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0">
                    <option value="">Semua</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((int) ($filters['location_id'] ?? 0) === (int) $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Zona</label>
                <select name="zone_id"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0 disabled:opacity-40 disabled:cursor-not-allowed"
                    @disabled(empty($filters['location_id']))>
                    <option value="">{{ empty($filters['location_id']) ? 'Pilih lokasi dulu' : 'Semua' }}</option>
                    @foreach ($zones as $z)
                        <option value="{{ $z->id }}" @selected((int) ($filters['zone_id'] ?? 0) === (int) $z->id)>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Lot</label>
                <select name="lot_id"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 text-sm h-9 focus:border-gray-400 focus:ring-0 disabled:opacity-40 disabled:cursor-not-allowed"
                    @disabled(empty($filters['zone_id']))>
                    <option value="">{{ empty($filters['zone_id']) ? 'Pilih zona dulu' : 'Semua' }}</option>
                    @foreach ($lots as $lot)
                        <option value="{{ $lot->id }}" @selected((int) ($filters['lot_id'] ?? 0) === (int) $lot->id)>{{ $lot->number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-6 flex items-center justify-end gap-2 pt-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center h-9 px-4 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200">
                    Reset
                </a>
                <x-button type="submit" color="primary"
                    class="h-9 px-5 bg-gray-800 hover:bg-gray-700 border-gray-800 text-sm font-medium">
                    Filter
                </x-button>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="mt-4 overflow-x-auto rounded-xl border border-gray-100 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Kode</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Nama</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Lokasi / Zona / Lot</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Tanggal &amp; Jam</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Fasilitas</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400">Status</th>
                    <th class="px-4 py-3 text-[10px] font-medium uppercase tracking-widest text-gray-400 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($bookings as $b)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $b->booking_code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $b->name }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $b->lot->zone->location->name }} / {{ $b->lot->zone->name }} / {{ $b->lot->number }}
                        </td>
                        <td class="px-4 py-3 text-gray-900">
                            {{ $b->booking_date->format('Y-m-d') }}
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ substr((string) $b->timeSlot->start_time, 0, 5) }} – {{ substr((string) $b->timeSlot->end_time, 0, 5) }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 leading-relaxed">
                            Tenda {{ ($b->facility->tent_count ?? 0) > 0 ? 'Ya' : 'Tidak' }},
                            Kursi {{ $b->facility->chair_count }},
                            Tong {{ $b->facility->burn_barrel_count }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($b->status === 'confirmed')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-800">Confirmed</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-800">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.bookings.show', $b) }}"
                               class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Tidak ada booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</x-layouts.admin>
