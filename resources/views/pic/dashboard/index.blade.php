<x-layouts.pic>
    <div>
        <h1 class="text-xl font-semibold">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Daftar booking (read-only).</p>
    </div>

    <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <form method="GET" action="{{ route('pic.dashboard') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Tanggal Dari</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Tanggal Sampai</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm">
                    <option value="">Semua</option>
                    <option value="confirmed" @selected($filters['status'] === 'confirmed')>Confirmed</option>
                    <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Lokasi</label>
                <select name="location_id" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm">
                    <option value="">Semua</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((int) ($filters['location_id'] ?? 0) === (int) $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Zona</label>
                <select name="zone_id" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm" @disabled(empty($filters['location_id']))>
                    <option value="">{{ empty($filters['location_id']) ? 'Pilih lokasi dulu' : 'Semua' }}</option>
                    @foreach ($zones as $z)
                        <option value="{{ $z->id }}" @selected((int) ($filters['zone_id'] ?? 0) === (int) $z->id)>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-900">Lot</label>
                <select name="lot_id" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm" @disabled(empty($filters['zone_id']))>
                    <option value="">{{ empty($filters['zone_id']) ? 'Pilih zona dulu' : 'Semua' }}</option>
                    @foreach ($lots as $lot)
                        <option value="{{ $lot->id }}" @selected((int) ($filters['lot_id'] ?? 0) === (int) $lot->id)>{{ $lot->number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-6 flex items-center justify-end gap-2">
                <a href="{{ route('pic.dashboard') }}" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-300">Reset</a>
                <x-button type="submit" color="primary">Filter</x-button>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg bg-white ring-1 ring-black/5">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 font-semibold">Kode</th>
                    <th class="px-4 py-3 font-semibold">Nama</th>
                    <th class="px-4 py-3 font-semibold">Lokasi/Zona/Lot</th>
                    <th class="px-4 py-3 font-semibold">Tanggal &amp; Jam</th>
                    <th class="px-4 py-3 font-semibold">Fasilitas</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($bookings as $b)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $b->booking_code }}</td>
                        <td class="px-4 py-3">{{ $b->name }}</td>
                        <td class="px-4 py-3">
                            {{ $b->lot->zone->location->name }} / {{ $b->lot->zone->name }} / {{ $b->lot->number }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $b->booking_date->format('Y-m-d') }}
                            <div class="text-xs text-gray-600">
                                {{ substr((string) $b->timeSlot->start_time, 0, 5) }} - {{ substr((string) $b->timeSlot->end_time, 0, 5) }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            Tenda {{ $b->facility->tent_count }},
                            Kursi {{ $b->facility->chair_count }},
                            Tong {{ $b->facility->burn_barrel_count }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($b->status === 'confirmed')
                                <span class="inline-flex items-center rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-semibold text-white">Confirmed</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-semibold text-white">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pic.bookings.show', $b) }}" class="rounded-md bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-600">Tidak ada booking.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
</x-layouts.pic>
