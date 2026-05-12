# Instruksi untuk codex

Dokumen ini adalah panduan implementasi teknis untuk Codex. Ikuti urutan ini saat membangun aplikasi.

---

## Urutan Implementasi

### Fase 1: Fondasi
1. Inisialisasi project Laravel 11 baru
2. Konfigurasi `.env` untuk PostgreSQL
3. Install package: `laravel/breeze`, `maatwebsite/excel`, `guzzlehttp/guzzle`
4. Setup Tailwind CSS
5. Buat semua migration (urutan sesuai `02_DATABASE_SCHEMA.md`)
6. Jalankan migration
7. Buat `AdminUserSeeder`
8. Jalankan seeder

### Fase 2: Models & Relationships
Buat semua Model dengan relasi:
- `Location` hasMany `Zone`
- `Zone` belongsTo `Location`, hasMany `Lot`
- `Lot` belongsTo `Zone`, hasMany `Booking`
- `Booking` belongsTo `Lot`, `TimeSlot`, hasOne `BookingFacility`
- `BookingFacility` belongsTo `Booking`
- `TimeSlot` hasMany `Booking`
- `User` (tambah field `role` ke model)

### Fase 3: Auth & Middleware
1. Setup Laravel Breeze (Blade)
2. Buat `RoleMiddleware`
3. Daftarkan middleware alias `role`
4. Modifikasi redirect setelah login berdasarkan role
5. Buat layout blade: `layouts/public.blade.php`, `layouts/admin.blade.php`, `layouts/pic.blade.php`

### Fase 4: Public Booking Form
1. Buat `BookingController` (public)
2. Implementasi AJAX endpoints:
   - `getZones(location_id)`
   - `getAvailableLots(zone_id, date, time_slot_id)`
   - `getTimeSlots()`
3. Buat view `public/booking/index.blade.php` — stepper 4 langkah
4. Implementasi `StoreBookingRequest` dengan validasi lengkap
5. Implementasi `BookingController@store` dengan:
   - Cek ketersediaan lot (unique constraint check)
   - Generate booking code
   - Simpan booking + fasilitas dalam transaction
6. Buat view `public/booking/success.blade.php`

### Fase 5: Admin — Lokasi & Time Slots
1. Buat `LocationImportService`
2. Buat `Admin\LocationController` (upload + list + delete)
3. Buat view `admin/locations/index.blade.php` dengan accordion + upload form
4. Buat `Admin\TimeSlotController` (CRUD)
5. Buat view `admin/time-slots/index.blade.php`

### Fase 6: Admin — Dashboard & Booking Management
1. Buat `Admin\DashboardController` dengan filter (date range, lokasi, zona, lot, status)
2. Buat view `admin/dashboard/index.blade.php` dengan tabel + filter
3. Buat `Admin\BookingController@show` dan `@cancel`
4. Buat view `admin/bookings/show.blade.php`

### Fase 7: Settings
1. Buat `DiscordSetting` model + seeder default
2. Buat `Admin\SettingController`
3. Buat view `admin/settings/index.blade.php`

### Fase 8: PIC Dashboard
1. Buat `Pic\DashboardController` (identik admin, tanpa aksi)
2. Buat `Pic\BookingController@show` (read-only)
3. Buat views PIC (bisa extend/reuse komponen admin tanpa tombol aksi)

### Fase 9: Discord Notification
1. Buat `BookingsExport` class (Maatwebsite)
2. Buat `ExcelExportService`
3. Buat `DiscordService` — kirim webhook dengan file attachment
4. Buat `SendDiscordNotificationJob`
5. Daftarkan di `Console/Kernel.php` dengan schedule dinamis dari DB

### Fase 10: Polish UI
1. Implementasi toast notification global (Alpine.js atau vanilla JS)
2. Implementasi modal konfirmasi global
3. Loading state pada semua tombol submit
4. Pastikan semua halaman mobile-responsive
5. Test semua flow end-to-end

---

## Catatan Penting untuk Codex

### Database Unique Constraint
```php
// Di migration bookings
$table->unique(['lot_id', 'time_slot_id', 'booking_date'], 'unique_lot_booking');
```

### Handle Race Condition di Controller
```php
try {
    DB::transaction(function () use ($validated) {
        // cek availability
        $exists = Booking::where('lot_id', $validated['lot_id'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'confirmed')
            ->lockForUpdate()
            ->exists();
        
        if ($exists) {
            throw new \Exception('Lot sudah dipesan.');
        }
        
        // simpan booking
    });
} catch (\Exception $e) {
    return back()->withErrors(['lot' => $e->getMessage()]);
}
```

### Discord Webhook dengan File
Discord webhook multipart (file attachment) menggunakan Guzzle:
```php
$client->post($webhookUrl, [
    'multipart' => [
        ['name' => 'payload_json', 'contents' => json_encode(['content' => $message])],
        ['name' => 'file', 'contents' => fopen($filePath, 'r'), 'filename' => 'booking.xlsx'],
    ]
]);
```

### Scheduler Dinamis dari DB
```php
// Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $settings = DiscordSetting::first();
    if (!$settings) return;

    $schedule->job(new SendDiscordNotificationJob('send_time_1'))
             ->dailyAt($settings->send_time_1);

    $schedule->job(new SendDiscordNotificationJob('send_time_2'))
             ->dailyAt($settings->send_time_2);
}
```

> ⚠️ **Catatan:** Jika jam setting diubah admin, scheduler perlu restart atau gunakan approach alternatif dengan `schedule:run` yang cek waktu secara dinamis dari DB setiap menit.

### Alternatif Scheduler Dinamis (Rekomendasi)
Gunakan 1 job yang berjalan setiap menit, cek apakah waktu sekarang cocok dengan setting:
```php
$schedule->job(new CheckAndSendDiscordJob())->everyMinute();
```

Di dalam `CheckAndSendDiscordJob`:
```php
$settings = DiscordSetting::first();
$now = now()->format('H:i');
if ($now === $settings->send_time_1 || $now === $settings->send_time_2) {
    // tentukan rentang & kirim
}
```

---

## Komponen UI yang Wajib Ada

### Toast Notification (Blade Component)
```html
<!-- resources/views/components/toast.blade.php -->
<!-- Dipanggil via JS setelah aksi berhasil/gagal -->
<!-- Warna: green untuk sukses, red untuk gagal -->
```

### Modal Konfirmasi (Blade Component)
```html
<!-- resources/views/components/confirm-modal.blade.php -->
<!-- Berisi: pesan konfirmasi, tombol Batal (abu) + Lanjutkan (merah/biru) -->
```

### Stepper (untuk form booking publik)
- Step indicator di atas (nomor + label)
- Navigasi prev/next
- Step saat ini di-highlight
- Step sebelumnya yang sudah diisi ditandai centang
