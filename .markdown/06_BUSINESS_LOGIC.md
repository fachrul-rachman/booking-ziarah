# Business Logic & Services

## 1. Booking Logic

### Validasi Lot Tersedia
Sebelum simpan booking, sistem wajib cek:
```
SELECT COUNT(*) FROM bookings
WHERE lot_id = ? 
  AND time_slot_id = ? 
  AND booking_date = ?
  AND status = 'confirmed'
```
Jika count > 0 → tolak, return error "Lot sudah dipesan untuk tanggal dan jam ini."

### Cascading Form (AJAX)
- Pilih Lokasi → fetch `/api/zones/{location_id}` → populate dropdown Zona
- Pilih Zona + Tanggal + Jam → fetch `/api/lots/available?zone_id=&date=&time_slot_id=` → tampilkan grid lot tersedia
- Lot yang sudah terisi (status confirmed) **tidak ikut ditampilkan**

### Booking Code Generation
Format: `ZR-YYYYMMDD-XXXX` (XXXX = 4 digit random alphanumeric uppercase)
- Generate di `BookingController@store` sebelum insert
- Pastikan unique (re-generate jika collision)

### Validasi Fasilitas
| Fasilitas | Min | Max | Default |
|-----------|-----|-----|---------|
| Tenda | 1 | 2 | 1 |
| Kursi | 5 | 10 | 5 |
| Tong Bakar | 0 | 2 | 0 |
| Meja Sembayang | - | - | false |
| Lampu | - | - | false |

---

## 2. Excel Import (Lokasi/Zona/Lot)

### Format File yang Diterima
| Kolom A | Kolom B | Kolom C |
|---------|---------|---------|
| Lokasi | Zona | Nomor Lot |
| Pemakaman Barat | Blok A | A-001 |
| Pemakaman Barat | Blok A | A-002 |
| Pemakaman Barat | Blok B | B-001 |

### Proses Import (`LocationImportService`)
1. Baca file Excel baris per baris
2. Untuk setiap baris:
   - `firstOrCreate` Location by name
   - `firstOrCreate` Zone by (location_id, name)
   - `firstOrCreate` Lot by (zone_id, number)
3. Jika ada error per baris (data tidak valid), catat dan tampilkan di preview
4. Tidak overwrite data yang sudah ada (idempotent)

---

## 3. Discord Notification Service

### Scheduler (`Console/Kernel.php`)
```php
$schedule->job(new SendDiscordNotificationJob('send_time_1'))
         ->dailyAt($settings->send_time_1);

$schedule->job(new SendDiscordNotificationJob('send_time_2'))
         ->dailyAt($settings->send_time_2);
```

### Rentang Data per Pengiriman
| Kirim | Rentang Data |
|-------|-------------|
| Jam send_time_1 (default 08:00) | 14:01 kemarin s/d 07:59 hari ini |
| Jam send_time_2 (default 14:00) | 08:01 hari ini s/d 13:59 hari ini |

### `SendDiscordNotificationJob`
1. Tentukan rentang waktu berdasarkan `send_time` yang dipanggil
2. Query bookings dalam rentang tersebut (by `booking_date` + `time_slot.start_time`)
3. Panggil `ExcelExportService` → generate file `.xlsx` di storage temp
4. Panggil `DiscordService` → kirim webhook dengan:
   - **File:** Excel attachment
   - **Message:** teks summary (lihat format di bawah)
5. Hapus file temp setelah terkirim

### Format Pesan Discord
```
📋 **Laporan Booking Ziarah**
📅 Periode: {tanggal_mulai} {jam_mulai} — {tanggal_selesai} {jam_selesai}

📊 **Ringkasan:**
• Total Booking: {n}
• Total Tenda: {n}
• Total Kursi: {n}
• Total Tong Bakar: {n}
• Meja Sembayang: {n} booking
• Lampu: {n} booking

📎 Detail lengkap terlampir.
```

### `ExcelExportService` — Kolom File Excel
| Kolom | Sumber |
|-------|--------|
| Kode Booking | bookings.booking_code |
| Tanggal Ziarah | bookings.booking_date |
| Jam | time_slots.start_time |
| Nama | bookings.name |
| Email | bookings.email |
| No. HP | bookings.phone |
| Lokasi | locations.name |
| Zona | zones.name |
| No. Lot | lots.number |
| Tenda | booking_facilities.tent_count |
| Kursi | booking_facilities.chair_count |
| Tong Bakar | booking_facilities.burn_barrel_count |
| Meja Sembayang | booking_facilities.prayer_table (Ya/Tidak) |
| Lampu | booking_facilities.lamp (Ya/Tidak) |
| Status | bookings.status |

---

## 4. RoleMiddleware

```php
// Middleware: RoleMiddleware
public function handle($request, Closure $next, string $role)
{
    if (!auth()->check() || auth()->user()->role !== $role) {
        abort(403);
    }
    return $next($request);
}
```

Registrasi di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => RoleMiddleware::class]);
})
```

---

## 5. Keamanan & Edge Cases

- **Race condition booking:** gunakan database-level unique constraint `(lot_id, time_slot_id, booking_date)` + handle `QueryException` di controller
- **Upload Excel besar:** set `max_execution_time` dan gunakan chunk reading Maatwebsite
- **Discord webhook gagal:** log error ke Laravel log, tidak throw exception ke user (silent fail)
- **Lot tampil di form:** query selalu filter `status = 'confirmed'` saja yang dianggap "terisi", lot cancelled kembali tersedia
