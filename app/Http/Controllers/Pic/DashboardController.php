<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Lot;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'lot_id' => ['nullable', 'integer', 'exists:lots,id'],
            'status' => ['nullable', 'in:confirmed,cancelled'],
        ]);

        $query = Booking::query()
            ->with(['lot.zone.location', 'timeSlot', 'facility'])
            ->orderByDesc('booking_date')
            ->orderByDesc('id');

        if (!empty($validated['date_from'])) {
            $query->whereDate('booking_date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('booking_date', '<=', $validated['date_to']);
        }

        if (!empty($validated['location_id'])) {
            $query->whereHas('lot.zone', fn ($q) => $q->where('location_id', $validated['location_id']));
        }

        if (!empty($validated['zone_id'])) {
            $query->whereHas('lot', fn ($q) => $q->where('zone_id', $validated['zone_id']));
        }

        if (!empty($validated['lot_id'])) {
            $query->where('lot_id', $validated['lot_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $bookings = $query->paginate(15)->withQueryString();

        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $zones = collect();
        if (!empty($validated['location_id'])) {
            $zones = Zone::query()
                ->where('location_id', $validated['location_id'])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $lots = collect();
        if (!empty($validated['zone_id'])) {
            $lots = Lot::query()
                ->where('zone_id', $validated['zone_id'])
                ->orderBy('number')
                ->get(['id', 'number']);
        }

        return view('pic.dashboard.index', [
            'bookings' => $bookings,
            'locations' => $locations,
            'zones' => $zones,
            'lots' => $lots,
            'filters' => [
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'zone_id' => $validated['zone_id'] ?? null,
                'lot_id' => $validated['lot_id'] ?? null,
                'status' => $validated['status'] ?? null,
            ],
        ]);
    }
}

