<x-layouts.pic>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Detail Booking</h1>
            <div class="mt-1 text-sm text-gray-600">{{ $booking->booking_code }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-4 ring-1 ring-black/5">
            <div class="text-sm font-semibold text-gray-900">Data Booking</div>
            <div class="mt-3 space-y-2 text-sm text-gray-900">
                <div><span class="font-semibold">Status:</span>
                    @if ($booking->status === 'confirmed')
                        <span class="ml-2 inline-flex items-center rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-semibold text-white">Confirmed</span>
                    @else
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-semibold text-white">Cancelled</span>
                    @endif
                </div>
                <div><span class="font-semibold">Tanggal:</span> {{ $booking->booking_date->format('Y-m-d') }}</div>
                <div><span class="font-semibold">Jam:</span> {{ substr((string) $booking->timeSlot->start_time, 0, 5) }} - {{ substr((string) $booking->timeSlot->end_time, 0, 5) }}</div>
                <div><span class="font-semibold">Lokasi:</span> {{ $booking->lot->zone->location->name }}</div>
                <div><span class="font-semibold">Zona:</span> {{ $booking->lot->zone->name }}</div>
                <div><span class="font-semibold">Lot:</span> {{ $booking->lot->number }}</div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-black/5">
            <div class="text-sm font-semibold text-gray-900">Data Pemesan</div>
            <div class="mt-3 space-y-2 text-sm text-gray-900">
                <div><span class="font-semibold">Nama:</span> {{ $booking->name }}</div>
                <div><span class="font-semibold">Hubungan:</span> {{ $booking->relationship ?? '-' }}</div>
                <div><span class="font-semibold">Email:</span> {{ $booking->email }}</div>
                <div><span class="font-semibold">No. HP:</span> {{ $booking->phone }}</div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-black/5">
            <div class="text-sm font-semibold text-gray-900">Fasilitas</div>
            <div class="mt-3 space-y-2 text-sm text-gray-900">
                <div><span class="font-semibold">Tenda:</span> {{ ($booking->facility->tent_count ?? 0) > 0 ? 'Ya' : 'Tidak' }}</div>
                <div><span class="font-semibold">Kursi:</span> {{ $booking->facility->chair_count }}</div>
                <div><span class="font-semibold">Tong Bakar:</span> {{ $booking->facility->burn_barrel_count }}</div>
                <div><span class="font-semibold">Meja Sembayang:</span> {{ $booking->facility->prayer_table ? 'Ya' : 'Tidak' }}</div>
                <div><span class="font-semibold">Lampu:</span> {{ $booking->facility->lamp ? 'Ya' : 'Tidak' }}</div>
            </div>
        </div>

        @if ($booking->status === 'cancelled')
            <div class="rounded-lg bg-white p-4 ring-1 ring-black/5">
                <div class="text-sm font-semibold text-gray-900">Info Pembatalan</div>
                <div class="mt-3 space-y-2 text-sm text-gray-900">
                    <div><span class="font-semibold">Dibatalkan pada:</span> {{ optional($booking->cancelled_at)->format('Y-m-d H:i') }}</div>
                    <div><span class="font-semibold">Dibatalkan oleh:</span> {{ $booking->cancelledBy?->name ?? '-' }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('pic.dashboard') }}" class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-300">Kembali</a>
    </div>
</x-layouts.pic>
