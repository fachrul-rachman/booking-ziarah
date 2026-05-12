<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->restrictOnDelete();
            $table->date('booking_date');
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['lot_id', 'time_slot_id', 'booking_date'], 'unique_lot_booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

