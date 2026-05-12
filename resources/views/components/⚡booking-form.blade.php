<script>
    (function () {
        const _dpFactory = function ({ minDate, selected }) {
            const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

            const parseYmd = (ymd) => {
                if (!ymd) return null;
                const parts = String(ymd).split('-');
                if (parts.length !== 3) return null;
                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const d = parseInt(parts[2], 10);
                if (!y || !m || !d) return null;
                return new Date(y, m - 1, d);
            };

            const toYmd = (date) => {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            const daysInMonth = (year, month) => new Date(year, month + 1, 0).getDate();
            const dayOfWeekMon0 = (date) => (date.getDay() + 6) % 7;

            const min = parseYmd(minDate);
            const selectedDate = parseYmd(selected) ?? null;
            const base = selectedDate ?? min ?? new Date();

            return {
                selected,
                viewYear: base.getFullYear(),
                viewMonth: base.getMonth(),
                get monthLabel() {
                    return `${monthNames[this.viewMonth]} ${this.viewYear}`;
                },
                get cells() {
                    const first = new Date(this.viewYear, this.viewMonth, 1);
                    const total = daysInMonth(this.viewYear, this.viewMonth);
                    const leading = dayOfWeekMon0(first);
                    const selectedParsed = parseYmd(this.selected);
                    const cells = [];
                    for (let i = 0; i < leading; i++) {
                        cells.push({ key: `e-${i}`, day: '', date: null, isDisabled: true, isSelected: false });
                    }
                    for (let d = 1; d <= total; d++) {
                        const dt = new Date(this.viewYear, this.viewMonth, d);
                        const ymd = toYmd(dt);
                        const isDisabled = min ? (dt < min) : false;
                        const isSelected = selectedParsed ? toYmd(selectedParsed) === ymd : false;
                        cells.push({ key: ymd, day: d, date: ymd, isDisabled, isSelected });
                    }
                    return cells;
                },
                prevMonth() {
                    const dt = new Date(this.viewYear, this.viewMonth - 1, 1);
                    this.viewYear = dt.getFullYear();
                    this.viewMonth = dt.getMonth();
                },
                nextMonth() {
                    const dt = new Date(this.viewYear, this.viewMonth + 1, 1);
                    this.viewYear = dt.getFullYear();
                    this.viewMonth = dt.getMonth();
                },
                select(ymd) {
                    if (!ymd) return;
                    this.selected = ymd;
                },
            };
        };

        window.datePicker = _dpFactory;

        const register = () => {
            if (window.Alpine) window.Alpine.data('datePicker', _dpFactory);
        };

        if (window.Alpine) {
            register();
        } else {
            document.addEventListener('alpine:init', register);
            document.addEventListener('alpine:initializing', register);
        }
    })();
</script>

<?php

use App\Models\Booking;
use App\Models\BookingFacility;
use App\Models\Location;
use App\Models\Lot;
use App\Models\TimeSlot;
use App\Models\Zone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public int $currentStep = 1;

    public ?int $location_id = null;
    public ?int $zone_id = null;
    public ?int $lot_id = null;
    public ?string $booking_date = null;
    public ?int $time_slot_id = null;

    public int $tent_count = 1;
    public int $chair_count = 5;
    public int $burn_barrel_count = 0;
    public bool $prayer_table = false;
    public bool $lamp = false;

    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public array $locations = [];
    public array $zones = [];
    public array $timeSlots = [];
    public array $availableLots = [];
    public string $lot_search = '';

    public function mount(): void
    {
        $this->restoreState();
        $this->loadLocations();
        $this->loadTimeSlots();
        $this->loadZones();
        $this->loadAvailableLots();
    }

    public function increment(string $prop, int $max): void
    {
        if (!property_exists($this, $prop)) {
            return;
        }

        $value = (int) ($this->{$prop} ?? 0);
        $this->{$prop} = min($max, $value + 1);
        $this->persistState();
    }

    public function decrement(string $prop, int $min): void
    {
        if (!property_exists($this, $prop)) {
            return;
        }

        $value = (int) ($this->{$prop} ?? 0);
        $this->{$prop} = max($min, $value - 1);
        $this->persistState();
    }

    public function toggle(string $prop): void
    {
        if (!property_exists($this, $prop)) {
            return;
        }

        $this->{$prop} = !((bool) ($this->{$prop} ?? false));
        $this->persistState();
    }

    private function restoreState(): void
    {
        $state = session()->get('booking_form_state');
        if (!is_array($state)) {
            return;
        }

        $this->currentStep = (int) ($state['currentStep'] ?? 1) ?: 1;
        $this->location_id = $state['location_id'] ?? null;
        $this->zone_id = $state['zone_id'] ?? null;
        $this->lot_id = $state['lot_id'] ?? null;
        $this->booking_date = $state['booking_date'] ?? null;
        $this->time_slot_id = $state['time_slot_id'] ?? null;

        $this->tent_count = (int) ($state['tent_count'] ?? 1) ?: 1;
        $this->chair_count = (int) ($state['chair_count'] ?? 5) ?: 5;
        $this->burn_barrel_count = (int) ($state['burn_barrel_count'] ?? 0) ?: 0;
        $this->prayer_table = (bool) ($state['prayer_table'] ?? false);
        $this->lamp = (bool) ($state['lamp'] ?? false);

        $this->name = (string) ($state['name'] ?? '');
        $this->email = (string) ($state['email'] ?? '');
        $this->phone = (string) ($state['phone'] ?? '');
    }

    private function persistState(): void
    {
        session()->put('booking_form_state', [
            'currentStep' => $this->currentStep,
            'location_id' => $this->location_id,
            'zone_id' => $this->zone_id,
            'lot_id' => $this->lot_id,
            'booking_date' => $this->booking_date,
            'time_slot_id' => $this->time_slot_id,

            'tent_count' => $this->tent_count,
            'chair_count' => $this->chair_count,
            'burn_barrel_count' => $this->burn_barrel_count,
            'prayer_table' => $this->prayer_table,
            'lamp' => $this->lamp,

            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
    }

    private function loadLocations(): void
    {
        $this->locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function loadZones(): void
    {
        if (!$this->location_id) {
            $this->zones = [];
            return;
        }

        $this->zones = Zone::query()
            ->where('location_id', $this->location_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function loadTimeSlots(): void
    {
        $this->timeSlots = TimeSlot::query()
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time'])
            ->toArray();
    }

    private function loadAvailableLots(): void
    {
        $this->availableLots = [];

        if (!$this->zone_id || !$this->booking_date || !$this->time_slot_id) {
            return;
        }

        $date = $this->booking_date;
        $timeSlotId = $this->time_slot_id;

        $this->availableLots = Lot::query()
            ->where('zone_id', $this->zone_id)
            ->where('is_active', true)
            ->whereDoesntHave('bookings', function ($q) use ($date, $timeSlotId) {
                $q->where('booking_date', $date)
                    ->where('time_slot_id', $timeSlotId)
                    ->where('status', 'confirmed');
            })
            ->orderBy('number')
            ->get(['id', 'number'])
            ->toArray();
    }

    public function updatedLocationId(): void
    {
        $this->zone_id = null;
        $this->lot_id = null;
        $this->lot_search = '';
        $this->loadZones();
        $this->loadAvailableLots();
        $this->persistState();
    }

    public function updatedZoneId(): void
    {
        $this->lot_id = null;
        $this->lot_search = '';
        $this->loadAvailableLots();
        $this->persistState();
    }

    public function updatedBookingDate(): void
    {
        $this->lot_id = null;
        $this->lot_search = '';
        $this->loadAvailableLots();
        $this->persistState();
    }

    public function updatedTimeSlotId(): void
    {
        $this->lot_id = null;
        $this->lot_search = '';
        $this->loadAvailableLots();
        $this->persistState();
    }

    public function selectLocation(int $id): void
    {
        $this->location_id = $id;
        $this->loadZones();
        $this->zone_id = null;
        $this->lot_id = null;
        $this->loadAvailableLots();
        $this->persistState();
    }

    public function selectLot(int $id): void
    {
        $this->lot_id = $id;
        $this->persistState();
    }

    public function selectTimeSlot(int $id): void
    {
        $this->time_slot_id = $id;
        $this->updatedTimeSlotId();
        $this->persistState();
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->currentStep));

        $this->currentStep = min(4, $this->currentStep + 1);
        $this->persistState();
    }

    public function prevStep(): void
    {
        $this->currentStep = max(1, $this->currentStep - 1);
        $this->persistState();
    }

    private function rulesForStep(int $step): array
    {
        $minDate = now()->addDays(2)->toDateString();

        return match ($step) {
            1 => [
                'location_id' => ['required', 'integer', 'exists:locations,id'],
            ],
            2 => [
                'zone_id' => ['required', 'integer', 'exists:zones,id'],
                'booking_date' => ['required', 'date', "after_or_equal:{$minDate}"],
                'time_slot_id' => ['required', 'integer', 'exists:time_slots,id'],
                'lot_id' => ['required', 'integer', 'exists:lots,id'],
            ],
            3 => [
                'tent_count' => ['required', 'integer', 'min:1', 'max:2'],
                'chair_count' => ['required', 'integer', 'min:5', 'max:10'],
                'burn_barrel_count' => ['required', 'integer', 'min:0', 'max:2'],
                'prayer_table' => ['required', 'boolean'],
                'lamp' => ['required', 'boolean'],
            ],
            4 => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
            ],
        };
    }

    private function allRules(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
            $this->rulesForStep(4),
        );
    }

    private function generateBookingCode(string $dateYmd): string
    {
        for ($i = 0; $i < 20; $i++) {
            $suffix = Str::upper(Str::random(4));
            $code = "ZR-{$dateYmd}-{$suffix}";

            $exists = Booking::query()->where('booking_code', $code)->exists();
            if (!$exists) {
                return $code;
            }
        }

        throw new \RuntimeException('Gagal generate kode booking, coba lagi.');
    }

    public function submit(): void
    {
        $validated = $this->validate($this->allRules());

        try {
            $code = DB::transaction(function () use ($validated) {
                $exists = Booking::query()
                    ->where('lot_id', $validated['lot_id'])
                    ->where('time_slot_id', $validated['time_slot_id'])
                    ->where('booking_date', $validated['booking_date'])
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw new \RuntimeException('Lot sudah dipesan untuk tanggal dan jam ini.');
                }

                $dateYmd = str_replace('-', '', (string) $validated['booking_date']);
                $bookingCode = $this->generateBookingCode($dateYmd);

                $booking = Booking::query()->create([
                    'booking_code' => $bookingCode,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'lot_id' => $validated['lot_id'],
                    'time_slot_id' => $validated['time_slot_id'],
                    'booking_date' => $validated['booking_date'],
                    'status' => 'confirmed',
                ]);

                BookingFacility::query()->create([
                    'booking_id' => $booking->id,
                    'tent_count' => $validated['tent_count'],
                    'chair_count' => $validated['chair_count'],
                    'burn_barrel_count' => $validated['burn_barrel_count'],
                    'prayer_table' => (bool) $validated['prayer_table'],
                    'lamp' => (bool) $validated['lamp'],
                ]);

                return $bookingCode;
            });
        } catch (QueryException $e) {
            report($e);
            $this->addError('form', 'Lot sudah dipesan untuk tanggal dan jam ini.');
            $this->currentStep = 2;
            $this->persistState();
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('form', $e->getMessage());
            return;
        }

        session()->forget('booking_form_state');
        $this->redirectRoute('booking.success', ['code' => $code]);
    }
};
?>

<div class="mx-auto max-w-lg pb-10">

    {{-- ══ STEPPER ══ --}}
    @php $stepLabels = ['Lokasi', 'Zona & Lot', 'Fasilitas', 'Data Diri']; @endphp
    <div class="mt-6 flex items-center px-1">
        @foreach ($stepLabels as $i => $label)
            @php $n = $i + 1; @endphp
            <div class="flex flex-col items-center flex-shrink-0" style="min-width:56px">
                <div @class([
                    'w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium border transition-all',
                    'bg-gray-800 border-gray-800 text-white' => $n <= $currentStep,
                    'bg-white border-gray-200 text-gray-400' => $n > $currentStep,
                ])>
                    @if ($n < $currentStep)
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        {{ $n }}
                    @endif
                </div>
                <span @class([
                    'mt-1 text-center text-[10px] leading-tight font-medium',
                    'text-gray-900' => $n === $currentStep,
                    'text-gray-500' => $n < $currentStep,
                    'text-gray-400' => $n > $currentStep,
                ]) style="max-width:52px">{{ $label }}</span>
            </div>
            @if ($n < count($stepLabels))
                <div @class([
                    'flex-1 mb-4 mx-0.5 transition-all',
                    'bg-gray-800' => $n < $currentStep,
                    'bg-gray-200' => $n >= $currentStep,
                ]) style="height:1px"></div>
            @endif
        @endforeach
    </div>

    {{-- ══ MAIN CARD ══ --}}
    <div class="mt-4 rounded-xl bg-white border border-gray-100 overflow-hidden">

        {{-- Global error --}}
        @error('form')
            <div class="mx-4 mt-4 flex gap-2 items-start bg-red-50 border border-red-100 rounded-lg p-3 text-sm text-red-700">
                <svg class="flex-shrink-0 mt-0.5" width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/><path d="M7 4v3.5M7 9.5v.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                {{ $message }}
            </div>
        @enderror


        {{-- ── STEP 1 — Lokasi ── --}}
        @if ($currentStep === 1)
            <div class="px-5 pt-5 pb-0">
                <h2 class="text-base font-medium text-gray-900">Pilih Lokasi</h2>
                <p class="text-sm text-gray-400 mt-0.5">Pilih area pemakaman yang ingin dikunjungi</p>
            </div>
            <div class="px-5 py-4">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($locations as $loc)
                        @php $sel = (int)($location_id ?? 0) === (int)$loc['id']; @endphp
                        <button
                            type="button"
                            wire:click="selectLocation({{ $loc['id'] }})"
                            class="flex items-center gap-3 rounded-lg border p-3.5 text-left transition-all
                                {{ $sel ? 'border-gray-800 bg-white' : 'border-gray-200 bg-gray-50 hover:border-gray-400' }}"
                        >
                            <div class="w-8 h-8 rounded-md flex items-center justify-center flex-shrink-0
                                {{ $sel ? 'bg-gray-800' : 'bg-white border border-gray-200' }}">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" class="{{ $sel ? 'text-white' : 'text-gray-500' }}">
                                    <path d="M8 1.5C5.515 1.5 3.5 3.515 3.5 6c0 3.375 4.5 9.5 4.5 9.5S12.5 9.375 12.5 6c0-2.485-2.015-4.5-4.5-4.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                    <circle cx="8" cy="6" r="1.5" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium {{ $sel ? 'text-gray-900' : 'text-gray-700' }}">{{ $loc['name'] }}</span>
                            @if ($sel)
                                <div class="ml-auto w-4 h-4 rounded-full bg-gray-800 flex items-center justify-center flex-shrink-0">
                                    <svg width="8" height="8" viewBox="0 0 9 9" fill="none"><path d="M1.5 4.5l2.5 2.5 3.5-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
                @error('location_id')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif


        {{-- ── STEP 2 — Zona, Tanggal, Jam, Lot ── --}}
        @if ($currentStep === 2)
            <div class="px-5 pt-5 pb-0">
                <h2 class="text-base font-medium text-gray-900">Zona, Tanggal &amp; Lot</h2>
                <p class="text-sm text-gray-400 mt-0.5">Tentukan zona, waktu, dan lot yang tersedia</p>
            </div>
            <div class="px-5 py-4 space-y-4">

                {{-- Zona --}}
                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Zona</label>
                    <select wire:model="zone_id"
                        class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:border-gray-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">Pilih zona…</option>
                        @foreach ($zones as $z)
                            <option value="{{ $z['id'] }}">{{ $z['name'] }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Tanggal kunjungan</label>
                    <div
                        class="rounded-lg border border-gray-200 overflow-hidden"
                        x-data="datePicker({
                            minDate: '{{ now()->addDays(2)->toDateString() }}',
                            selected: @entangle('booking_date').live,
                        })"
                    >
                        <div class="flex items-center justify-between px-3 py-2.5 bg-gray-50 border-b border-gray-200">
                            <button type="button"
                                class="w-7 h-7 rounded-md border border-gray-200 bg-white hover:bg-gray-100 flex items-center justify-center transition-colors"
                                @click="prevMonth()">
                                <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><path d="M8 10L4.5 6.5 8 3" stroke="#374151" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <span class="text-sm font-medium text-gray-800" x-text="monthLabel"></span>
                            <button type="button"
                                class="w-7 h-7 rounded-md border border-gray-200 bg-white hover:bg-gray-100 flex items-center justify-center transition-colors"
                                @click="nextMonth()">
                                <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><path d="M5 3l3.5 3.5L5 10" stroke="#374151" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 px-3 pt-2.5 pb-1">
                            @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d)
                                <div class="text-center text-[10px] font-medium text-gray-400 uppercase">{{ $d }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 px-3 pb-3 gap-y-0.5">
                            <template x-for="cell in cells" :key="cell.key">
                                <button
                                    type="button"
                                    class="mx-auto w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium transition-colors"
                                    :class="{
                                        'bg-gray-800 text-white': cell.isSelected,
                                        'text-gray-300 cursor-default pointer-events-none': cell.isDisabled && !cell.isSelected,
                                        'text-gray-700 hover:bg-gray-100': !cell.isDisabled && !cell.isSelected
                                    }"
                                    :disabled="cell.isDisabled"
                                    @click="select(cell.date)"
                                    x-text="cell.day"
                                ></button>
                            </template>
                        </div>
                        <div class="px-3 py-2 bg-amber-50 border-t border-amber-100 flex items-center gap-2">
                            <svg width="12" height="12" viewBox="0 0 13 13" fill="none" class="text-amber-500 flex-shrink-0"><circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.3"/><path d="M6.5 4v3.5M6.5 9v.3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <span class="text-xs text-amber-700">Minimal pemesanan H+2 dari hari ini</span>
                        </div>
                    </div>
                    @error('booking_date') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Jam --}}
                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5">Jam kunjungan</label>
                    <div class="grid grid-cols-3 gap-1.5 sm:grid-cols-4">
                        @foreach ($timeSlots as $ts)
                            @php $tsSelected = (int)($time_slot_id ?? 0) === (int)$ts['id']; @endphp
                            <button
                                type="button"
                                wire:click="selectTimeSlot({{ $ts['id'] }})"
                                class="rounded-lg border py-2.5 text-sm font-medium transition-all
                                    {{ $tsSelected
                                        ? 'border-gray-800 bg-gray-800 text-white'
                                        : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-gray-400' }}"
                            >
                                {{ substr((string) $ts['start_time'], 0, 5) }}
                            </button>
                        @endforeach
                    </div>
                    @error('time_slot_id') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Lot --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400">Nomor lot</label>
                        @if ($zone_id && $booking_date && $time_slot_id)
                            <span class="text-[10px] font-medium text-gray-500 bg-gray-100 border border-gray-200 rounded-full px-2 py-0.5">
                                {{ count($availableLots) }} tersedia
                            </span>
                        @endif
                    </div>

                    @if (!$zone_id || !$booking_date || !$time_slot_id)
                        <div class="flex gap-2 items-start bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <svg class="text-gray-400 flex-shrink-0 mt-0.5" width="13" height="13" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3"/><path d="M7 4.5v4M7 10v.3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <p class="text-sm text-gray-500">Pilih zona, tanggal, dan jam terlebih dahulu.</p>
                        </div>
                    @else
                        <div class="relative mb-2">
                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="13" height="13" viewBox="0 0 15 15" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M10.5 10.5l2.5 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                            <input
                                wire:model.live.debounce.200ms="lot_search"
                                type="text"
                                placeholder="Cari nomor lot…"
                                class="block w-full rounded-lg border border-gray-200 bg-gray-50 pl-8 pr-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-0 focus:outline-none transition-colors"
                            >
                        </div>

                        @php
                            $query = trim($lot_search ?? '');
                            $lotsToShow = $availableLots;
                            if ($query !== '') {
                                $lotsToShow = array_values(array_filter($availableLots, function ($lot) use ($query) {
                                    $num = (string) ($lot['number'] ?? '');
                                    return Str::contains(mb_strtolower($num), mb_strtolower($query));
                                }));
                            }
                        @endphp

                        @if (count($lotsToShow) === 0)
                            <div class="py-6 text-center text-sm text-gray-400">Lot tidak ditemukan.</div>
                        @else
                            <div class="grid grid-cols-4 gap-1.5 max-h-44 overflow-y-auto sm:grid-cols-5">
                                @foreach ($lotsToShow as $lot)
                                    @php $lotSel = (int)($lot_id ?? 0) === (int)$lot['id']; @endphp
                                    <button
                                        type="button"
                                        wire:click="selectLot({{ $lot['id'] }})"
                                        class="rounded-lg border py-2 font-mono text-xs font-medium transition-all
                                            {{ $lotSel
                                                ? 'border-gray-800 bg-gray-800 text-white'
                                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-400' }}"
                                    >
                                        {{ $lot['number'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    @error('lot_id') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        @endif


        {{-- ── STEP 3 — Fasilitas ── --}}
        @if ($currentStep === 3)
            <div class="px-5 pt-5 pb-0">
                <h2 class="text-base font-medium text-gray-900">Fasilitas</h2>
                <p class="text-sm text-gray-400 mt-0.5">Pilih fasilitas yang Anda butuhkan</p>
            </div>
            <div class="px-5 py-4 space-y-4">

                {{-- Counters --}}
                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-2">Jumlah item</label>
                    <div class="rounded-lg border border-gray-200 overflow-hidden divide-y divide-gray-100">
                        @foreach ([
                            ['label' => 'Tenda',      'hint' => 'Min 1 — Maks 2',  'prop' => 'tent_count',        'min' => 1, 'max' => 2,  'val' => $tent_count],
                            ['label' => 'Kursi',      'hint' => 'Min 5 — Maks 10', 'prop' => 'chair_count',       'min' => 5, 'max' => 10, 'val' => $chair_count],
                            ['label' => 'Tong Bakar', 'hint' => 'Min 0 — Maks 2',  'prop' => 'burn_barrel_count', 'min' => 0, 'max' => 2,  'val' => $burn_barrel_count],
                        ] as $item)
                            <div class="flex items-center justify-between bg-white px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item['label'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item['hint'] }}</p>
                                </div>
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button"
                                        wire:click="decrement('{{ $item['prop'] }}', {{ $item['min'] }})"
                                        @if ($item['val'] <= $item['min']) disabled @endif
                                        class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-600 text-base hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        −
                                    </button>
                                    <span class="w-9 text-center text-sm font-medium text-gray-900 select-none">{{ $item['val'] }}</span>
                                    <button type="button"
                                        wire:click="increment('{{ $item['prop'] }}', {{ $item['max'] }})"
                                        @if ($item['val'] >= $item['max']) disabled @endif
                                        class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-600 text-base hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        +
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('tent_count')        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('chair_count')       <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('burn_barrel_count') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Toggles --}}
                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-2">Perlengkapan tambahan</label>
                    <div class="space-y-2">

                        <button type="button" wire:click="toggle('prayer_table')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg border cursor-pointer transition-all
                                {{ $prayer_table ? 'border-gray-800 bg-white' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}">
                            <span class="text-sm font-medium text-gray-800">Meja Sembayang</span>
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-5 rounded-full transition-colors {{ $prayer_table ? 'bg-gray-800' : 'bg-gray-200' }}"></div>
                                <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all {{ $prayer_table ? 'left-[22px]' : 'left-0.5' }}"></div>
                            </div>
                        </button>

                        <button type="button" wire:click="toggle('lamp')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg border cursor-pointer transition-all
                                {{ $lamp ? 'border-gray-800 bg-white' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}">
                            <span class="text-sm font-medium text-gray-800">Lampu</span>
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-5 rounded-full transition-colors {{ $lamp ? 'bg-gray-800' : 'bg-gray-200' }}"></div>
                                <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all {{ $lamp ? 'left-[22px]' : 'left-0.5' }}"></div>
                            </div>
                        </button>

                    </div>
                </div>

            </div>
        @endif


        {{-- ── STEP 4 — Data Diri & Konfirmasi ── --}}
        @if ($currentStep === 4)
            <div class="px-5 pt-5 pb-0">
                <h2 class="text-base font-medium text-gray-900">Data Diri</h2>
                <p class="text-sm text-gray-400 mt-0.5">Isi informasi kontak untuk konfirmasi booking</p>
            </div>
            <div class="px-5 py-4 space-y-4">

                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5" for="bk-name">Nama lengkap</label>
                    <input id="bk-name" wire:model="name" type="text" placeholder="cth. Budi Santoso"
                        class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-0 focus:outline-none transition-colors">
                    @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5" for="bk-email">Alamat email</label>
                    <input id="bk-email" wire:model="email" type="email" placeholder="cth. budi@email.com"
                        class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-0 focus:outline-none transition-colors">
                    @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-medium uppercase tracking-widest text-gray-400 mb-1.5" for="bk-phone">Nomor HP</label>
                    <input id="bk-phone" wire:model="phone" type="text" placeholder="cth. 0812-3456-7890"
                        class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-0 focus:outline-none transition-colors">
                    @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Ringkasan --}}
                @php
                    $loc = collect($locations)->firstWhere('id', $location_id);
                    $zn  = collect($zones)->firstWhere('id', $zone_id);
                    $lt  = collect($availableLots)->firstWhere('id', $lot_id);
                    $t   = collect($timeSlots)->firstWhere('id', $time_slot_id);
                @endphp
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gray-800 px-4 py-2.5">
                        <p class="text-[10px] font-medium uppercase tracking-widest text-gray-400">Ringkasan Booking</p>
                    </div>
                    <div class="bg-white divide-y divide-gray-100">
                        @foreach([
                            'Lokasi'  => is_array($loc) ? ($loc['name'] ?? '—') : '—',
                            'Zona'    => is_array($zn)  ? ($zn['name']  ?? '—') : '—',
                            'Lot'     => is_array($lt)  ? ($lt['number'] ?? '—') : '—',
                            'Tanggal' => $booking_date ?? '—',
                        ] as $k => $v)
                            <div class="flex items-center px-4 py-2.5 gap-3">
                                <span class="w-14 flex-shrink-0 text-xs text-gray-400">{{ $k }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ $v }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center px-4 py-2.5 gap-3">
                            <span class="w-14 flex-shrink-0 text-xs text-gray-400">Jam</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if (is_array($t))
                                    {{ substr((string)($t['start_time'] ?? ''), 0, 5) }} – {{ substr((string)($t['end_time'] ?? ''), 0, 5) }}
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="flex items-start px-4 py-2.5 gap-3">
                            <span class="w-14 flex-shrink-0 text-xs text-gray-400 mt-0.5">Fasilitas</span>
                            <span class="text-xs text-gray-700 leading-relaxed">
                                Tenda {{ $tent_count }} · Kursi {{ $chair_count }} · Tong {{ $burn_barrel_count }}<br>
                                Meja: {{ $prayer_table ? 'Ya' : 'Tidak' }} · Lampu: {{ $lamp ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-800 px-5 py-3.5 text-sm font-medium text-white hover:bg-gray-700 active:scale-[.99] transition-all">
                    Konfirmasi &amp; Kirim Booking
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2.5 8h11M8 2.5l5.5 5.5L8 13.5" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

            </div>
        @endif


        {{-- ── NAV BAR ── --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100 bg-gray-50">
            <button
                type="button"
                wire:click="prevStep"
                @if ($currentStep === 1) disabled @endif
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all active:scale-[.98]">
                <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><path d="M8.5 10.5L4.5 6.5l4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali
            </button>

            @php
                $canNext = match ($currentStep) {
                    1 => (bool) $location_id,
                    2 => (bool) $zone_id && (bool) $booking_date && (bool) $time_slot_id && (bool) $lot_id,
                    3 => true,
                    default => false,
                };
            @endphp

            @if ($currentStep < 4)
                <button
                    type="button"
                    wire:click="nextStep"
                    @if (!$canNext) disabled @endif
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-800 px-5 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all active:scale-[.98]">
                    Lanjut
                    <svg width="12" height="12" viewBox="0 0 13 13" fill="none"><path d="M4.5 2.5l4 4-4 4" stroke="white" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @endif
        </div>

    </div>{{-- /card --}}

</div>{{-- /wrap --}}