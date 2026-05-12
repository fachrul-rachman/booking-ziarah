<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(Booking $booking): View
    {
        $booking->load(['lot.zone.location', 'timeSlot', 'facility', 'cancelledBy']);

        return view('pic.bookings.show', [
            'booking' => $booking,
        ]);
    }
}

