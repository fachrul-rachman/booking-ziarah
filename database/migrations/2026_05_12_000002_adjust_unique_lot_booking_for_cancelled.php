<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Drop old constraint (applies to all statuses) and replace with partial unique index
        // so cancelled bookings don't lock the slot.
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS unique_lot_booking');
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS unique_lot_booking_confirmed ON bookings (lot_id, time_slot_id, booking_date) WHERE status = 'confirmed'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS unique_lot_booking_confirmed');

        // Restore old constraint.
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT unique_lot_booking UNIQUE (lot_id, time_slot_id, booking_date)');
    }
};

