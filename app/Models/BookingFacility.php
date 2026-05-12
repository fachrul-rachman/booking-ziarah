<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'tent_count',
        'chair_count',
        'burn_barrel_count',
        'prayer_table',
        'lamp',
    ];

    protected function casts(): array
    {
        return [
            'prayer_table' => 'boolean',
            'lamp' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

