<x-layouts.admin>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-lg font-medium text-gray-900">Detail Booking</h1>
            <div class="mt-0.5 font-mono text-xs tracking-wide text-gray-400">{{ $booking->booking_code }}</div>
        </div>

        @if ($booking->status === 'confirmed')
            <form id="cancel-booking-form" action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <x-button
                    type="button"
                    color="danger"
                    class="inline-flex items-center gap-1.5 bg-red-50 text-red-800 border border-red-200 hover:bg-red-100 text-sm font-medium"
                    x-on:click="window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: { title: 'Batalkan Booking', message: 'Booking ini akan dibatalkan. Lanjutkan?', confirmText: 'Batalkan', confirmColor: 'bg-red-600', formId: 'cancel-booking-form' } }))"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Batalkan Booking
                </x-button>
            </form>
        @endif
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-2">

        {{-- Data Booking --}}
        <div class="rounded-xl bg-white border border-gray-100 p-5">
            <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-100">
                Data Booking
            </div>
            <dl class="mt-1 divide-y divide-gray-50 text-sm">
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        @if ($booking->status === 'confirmed')
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-800">Confirmed</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-800">Cancelled</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Tanggal</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->booking_date->format('Y-m-d') }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Jam</dt>
                    <dd class="font-medium text-gray-900">{{ substr((string) $booking->timeSlot->start_time, 0, 5) }} – {{ substr((string) $booking->timeSlot->end_time, 0, 5) }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Lokasi</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->lot->zone->location->name }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Zona</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->lot->zone->name }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Lot</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->lot->number }}</dd>
                </div>
            </dl>
        </div>

        {{-- Data Pemesan --}}
        <div class="rounded-xl bg-white border border-gray-100 p-5">
            <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-100">
                Data Pemesan
            </div>
            <dl class="mt-1 divide-y divide-gray-50 text-sm">
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->name }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Hubungan</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->relationship ?? '-' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->email }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">No. HP</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->phone }}</dd>
                </div>
            </dl>
        </div>

        {{-- Fasilitas --}}
        <div class="rounded-xl bg-white border border-gray-100 p-5">
            <div class="text-[10px] font-medium uppercase tracking-widest text-gray-400 pb-3 border-b border-gray-100">
                Fasilitas
            </div>
            <dl class="mt-1 divide-y divide-gray-50 text-sm">
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Tenda</dt>
                    <dd class="font-medium text-gray-900">{{ ($booking->facility->tent_count ?? 0) > 0 ? 'Ya' : 'Tidak' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Kursi</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->facility->chair_count }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Tong Bakar</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->facility->burn_barrel_count }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Meja Sembayang</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->facility->prayer_table ? 'Ya' : 'Tidak' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-gray-500">Lampu</dt>
                    <dd class="font-medium text-gray-900">{{ $booking->facility->lamp ? 'Ya' : 'Tidak' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Info Pembatalan (conditional) --}}
        @if ($booking->status === 'cancelled')
            <div class="rounded-xl bg-red-50 border border-red-100 p-5">
                <div class="text-[10px] font-medium uppercase tracking-widest text-red-400 pb-3 border-b border-red-100">
                    Info Pembatalan
                </div>
                <dl class="mt-1 divide-y divide-red-100 text-sm">
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-red-400">Dibatalkan pada</dt>
                        <dd class="font-medium text-red-900">{{ optional($booking->cancelled_at)->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-red-400">Dibatalkan oleh</dt>
                        <dd class="font-medium text-red-900">{{ $booking->cancelledBy?->name ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        @endif

    </div>

    <div class="mt-5">
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali
        </a>
    </div>
</x-layouts.admin>
