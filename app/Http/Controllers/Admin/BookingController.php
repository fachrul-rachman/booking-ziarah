<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(Booking $booking): View
    {
        $booking->load(['lot.zone.location', 'timeSlot', 'facility', 'cancelledBy']);

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status === 'cancelled') {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'Booking sudah dibatalkan.',
            ]);
        }

        DB::transaction(function () use ($request, $booking) {
            $booking->refresh();

            if ($booking->status === 'cancelled') {
                return;
            }

            $booking->status = 'cancelled';
            $booking->cancelled_by = $request->user()?->id;
            $booking->cancelled_at = now();
            $booking->save();
        });

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Booking berhasil dibatalkan.',
            ]);
    }
}

