<x-layouts.public>
    <div class="rounded-lg bg-white p-5 ring-1 ring-black/5">
        <h1 class="text-xl font-semibold text-gray-900">Booking Berhasil</h1>
        <p class="mt-2 text-sm text-gray-700">Simpan kode booking Anda.</p>

        <div class="mt-4 rounded-md bg-gray-50 p-4">
            <div class="text-xs font-semibold text-gray-600">KODE BOOKING</div>
            <div class="mt-1 text-2xl font-bold tracking-wide text-blue-700">{{ $booking->booking_code }}</div>
        </div>

        <div class="mt-5 space-y-2 text-sm text-gray-900">
            <div><span class="font-semibold">Nama:</span> {{ $booking->name }}</div>
            <div><span class="font-semibold">Hubungan:</span> {{ $booking->relationship ?? '-' }}</div>
            <div><span class="font-semibold">Email:</span> {{ $booking->email }}</div>
            <div><span class="font-semibold">No. HP:</span> {{ $booking->phone }}</div>
            <div><span class="font-semibold">Lokasi:</span> {{ $booking->lot->zone->location->name }}</div>
            <div><span class="font-semibold">Zona:</span> {{ $booking->lot->zone->name }}</div>
            <div><span class="font-semibold">No. Lot:</span> {{ $booking->lot->number }}</div>
            <div><span class="font-semibold">Tanggal:</span> {{ $booking->booking_date->format('Y-m-d') }}</div>
            <div><span class="font-semibold">Jam:</span> {{ substr((string) $booking->timeSlot->start_time, 0, 5) }} - {{ substr((string) $booking->timeSlot->end_time, 0, 5) }}</div>

            <div class="pt-2">
                <div class="font-semibold">Fasilitas</div>
                <div class="mt-1 text-gray-800">
                    Tenda: {{ ($booking->facility->tent_count ?? 0) > 0 ? 'Ya' : 'Tidak' }},
                    Kursi: {{ $booking->facility->chair_count }},
                    Tong Bakar: {{ $booking->facility->burn_barrel_count }},
                    Meja Sembayang: {{ $booking->facility->prayer_table ? 'Ya' : 'Tidak' }},
                    Lampu: {{ $booking->facility->lamp ? 'Ya' : 'Tidak' }}
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ url('/') }}" class="inline-flex rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Kembali ke Beranda</a>
        </div>
    </div>
</x-layouts.public>
