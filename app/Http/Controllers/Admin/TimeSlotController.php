<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeSlotController extends Controller
{
    public function index(): View
    {
        $timeSlots = TimeSlot::query()
            ->orderBy('start_time')
            ->get();

        return view('admin.time-slots.index', [
            'timeSlots' => $timeSlots,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_time' => ['required', 'date_format:H:i', 'unique:time_slots,start_time'],
        ]);

        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = $start->copy()->addHour();

        TimeSlot::query()->create([
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'is_active' => true,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Time slot berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $validated = $request->validate([
            'start_time' => ['nullable', 'date_format:H:i', Rule::unique('time_slots', 'start_time')->ignore($timeSlot->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('start_time', $validated) && $validated['start_time'] !== null) {
            $start = Carbon::createFromFormat('H:i', $validated['start_time']);
            $timeSlot->start_time = $start->format('H:i');
            $timeSlot->end_time = $start->copy()->addHour()->format('H:i');
        }

        if (array_key_exists('is_active', $validated)) {
            $timeSlot->is_active = (bool) $validated['is_active'];
        }

        $timeSlot->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Time slot berhasil diupdate.',
        ]);
    }

    public function destroy(TimeSlot $timeSlot): RedirectResponse
    {
        $timeSlot->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Time slot berhasil dihapus.',
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'range_start' => ['required', 'date_format:H:i'],
            'range_end' => ['required', 'date_format:H:i'],
        ]);

        $start = Carbon::createFromFormat('H:i', $validated['range_start']);
        $end = Carbon::createFromFormat('H:i', $validated['range_end']);

        $startMinutes = ((int) $start->format('H')) * 60 + (int) $start->format('i');
        $endMinutes = ((int) $end->format('H')) * 60 + (int) $end->format('i');

        $fullDay = $startMinutes === $endMinutes;

        $created = 0;
        $skipped = 0;

        $cursor = $start->copy();
        for ($i = 0; $i < 24; $i++) {
            $cursorMinutes = ((int) $cursor->format('H')) * 60 + (int) $cursor->format('i');
            if (!$fullDay && $i > 0 && $cursorMinutes === $endMinutes) {
                break;
            }

            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addHour();

            $slot = TimeSlot::query()->firstOrCreate(
                ['start_time' => $slotStart->format('H:i')],
                [
                    'end_time' => $slotEnd->format('H:i'),
                    'is_active' => true,
                ],
            );

            if ($slot->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }

            $cursor->addHour();
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Generate selesai: {$created} slot dibuat, {$skipped} sudah ada.",
        ]);
    }
}
