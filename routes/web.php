<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\TimeSlotController as AdminTimeSlotController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Pic\DashboardController as PicDashboardController;
use App\Http\Controllers\Pic\BookingController as PicBookingController;
use Illuminate\Support\Facades\Route;
use App\Models\Booking;

Route::get('/', function () {
    return view('public.booking.index');
});

Route::get('/booking/success/{code}', function (string $code) {
    $booking = Booking::query()
        ->with(['lot.zone.location', 'timeSlot', 'facility'])
        ->where('booking_code', $code)
        ->firstOrFail();

    return view('public.booking.success', ['booking' => $booking]);
})->name('booking.success');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/locations', [AdminLocationController::class, 'index'])->name('admin.locations.index');
    Route::post('/locations/upload', [AdminLocationController::class, 'upload'])->name('admin.locations.upload');
    Route::get('/locations/imports/{import}', [AdminLocationController::class, 'importStatus'])->name('admin.locations.imports.status');
    Route::delete('/locations/{location}', [AdminLocationController::class, 'destroy'])->name('admin.locations.destroy');

    Route::get('/time-slots', [AdminTimeSlotController::class, 'index'])->name('admin.time-slots.index');
    Route::post('/time-slots', [AdminTimeSlotController::class, 'store'])->name('admin.time-slots.store');
    Route::post('/time-slots/generate', [AdminTimeSlotController::class, 'generate'])->name('admin.time-slots.generate');
    Route::patch('/time-slots/{timeSlot}', [AdminTimeSlotController::class, 'update'])->name('admin.time-slots.update');
    Route::delete('/time-slots/{timeSlot}', [AdminTimeSlotController::class, 'destroy'])->name('admin.time-slots.destroy');

    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('admin.bookings.show');
    Route::patch('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('admin.bookings.cancel');

    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');

    Route::get('/settings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
    Route::patch('/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');
});

Route::prefix('pic')->middleware(['auth', 'role:pic'])->group(function () {
    Route::get('/dashboard', [PicDashboardController::class, 'index'])->name('pic.dashboard');
    Route::get('/bookings/{booking}', [PicBookingController::class, 'show'])->name('pic.bookings.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
