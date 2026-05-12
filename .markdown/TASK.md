# TASKS.md — Booking Ziarah

Dokumen ini adalah panduan pengerjaan Codex, permodul dan berurutan.
Selesaikan dan test setiap modul sebelum lanjut ke modul berikutnya.

---

## M01 — Fondasi

### Tasks
- [x] Inisialisasi project Laravel
- [x] Konfigurasi `.env` untuk PostgreSQL
- [x] Install Livewire: `composer require livewire/livewire`
- [x] Install Laravel Excel: `composer require maatwebsite/excel`
- [x] Install Guzzle: `composer require guzzlehttp/guzzle`
- [x] Install Laravel Breeze (Blade): `composer require laravel/breeze --dev` → `php artisan breeze:install blade`
- [x] Setup Tailwind CSS via npm
- [x] `npm install && npm run build`
- [x] Pastikan halaman default Laravel muncul di browser

### Test
- [x] `php artisan serve` berjalan tanpa error
- [x] Halaman `/` terbuka di browser
- [x] `npm run build` sukses tanpa error

---

## M02 — Database & Models

### Tasks
- [x] Buat migration `create_users_table` — tambah kolom `role` enum('admin','pic')
- [x] Buat migration `create_locations_table`
- [x] Buat migration `create_zones_table`
- [x] Buat migration `create_lots_table`
- [x] Buat migration `create_time_slots_table`
- [x] Buat migration `create_bookings_table` — termasuk unique constraint `(lot_id, time_slot_id, booking_date)`
- [x] Buat migration `create_booking_facilities_table`
- [x] Buat migration `create_discord_settings_table`
- [x] Jalankan `php artisan migrate`
- [x] Buat Model: `Location`, `Zone`, `Lot`, `TimeSlot`, `Booking`, `BookingFacility`, `DiscordSetting`
- [x] Definisikan semua relasi di setiap model (hasMany, belongsTo, hasOne)
- [x] Update model `User` — tambah field `role`, tambah fillable
- [ ] Buat `AdminUserSeeder` — buat 1 user admin default (email: `admin@ziarah.local`, password: `Admin@12345`, role: `admin`)
- [x] Buat `DiscordSettingSeeder` — insert 1 row default (send_time_1: `08:00`, send_time_2: `14:00`)
- [x] Jalankan seeder: `php artisan db:seed`

### Test
- [x] `php artisan migrate` sukses tanpa error
- [x] Cek tabel di PostgreSQL — semua tabel terbentuk
- [x] Cek unique constraint `unique_lot_booking` ada di tabel `bookings`
- [x] `php artisan db:seed` sukses
- [x] Cek tabel `users` — ada 1 row admin
- [x] Cek tabel `discord_settings` — ada 1 row default

---

## M03 — Auth & Middleware

### Tasks
- [x] Pastikan Breeze sudah terinstall dan route `/login`, `/logout` tersedia
- [x] Hapus route register (tidak dibutuhkan)
- [x] Buat `RoleMiddleware` di `app/Http/Middleware/RoleMiddleware.php`
- [x] Daftarkan alias `role` di `bootstrap/app.php`
- [x] Modifikasi `AuthenticatedSessionController` — redirect setelah login berdasarkan role:
  - `admin` → `/admin/dashboard`
  - `pic` → `/pic/dashboard`
- [x] Buat layout `resources/views/layouts/public.blade.php`
- [x] Buat layout `resources/views/layouts/admin.blade.php` — dengan sidebar navigasi
- [x] Buat layout `resources/views/layouts/pic.blade.php` — dengan sidebar navigasi (tanpa menu aksi)
- [x] Buat placeholder halaman `/admin/dashboard` (kosong dulu, hanya judul)
- [x] Buat placeholder halaman `/pic/dashboard` (kosong dulu, hanya judul)
- [x] Daftarkan route `/admin/*` dengan middleware `auth` + `role:admin`
- [x] Daftarkan route `/pic/*` dengan middleware `auth` + `role:pic`

### Test
- [x] Akses `/login` → halaman login tampil
- [x] Login dengan `admin@ziarah.local` / `Admin@12345` → redirect ke `/admin/dashboard`
- [ ] Logout → redirect ke `/login`
- [x] Akses `/admin/dashboard` tanpa login → redirect ke `/login`
- [x] Login sebagai admin, akses `/pic/dashboard` → 403
- [ ] Buat 1 user PIC manual via tinker (`role: 'pic'`), login → redirect ke `/pic/dashboard`

---

## M04 — UI Components

### Tasks
- [x] Buat Blade component `resources/views/components/toast.blade.php`
  - Props: `type` (success/error), `message`
  - Warna: hijau untuk sukses, merah untuk error
  - Auto-dismiss setelah 4 detik
  - Posisi: pojok kanan atas, fixed
- [x] Buat Blade component `resources/views/components/confirm-modal.blade.php`
  - Props: `title`, `message`, `confirm-text`, `confirm-color`
  - Tombol: Batal (abu) + Lanjutkan (warna sesuai prop)
  - Trigger via JS / Alpine.js
- [x] Buat Blade component `resources/views/components/stepper.blade.php`
  - Props: `steps` (array label), `current-step`
  - Step selesai: ikon centang
  - Step aktif: highlight warna primary
- [x] Buat Blade component `resources/views/components/button.blade.php`
  - Props: `type`, `color`, `loading` (bool)
  - Tampilkan spinner + disabled saat `loading = true`
- [x] Integrasikan Alpine.js (via CDN atau npm) untuk interaktivitas modal & toast
- [x] Include komponen toast & modal di semua layout (public, admin, pic)

### Test
- [ ] Buat halaman test sementara, tampilkan semua komponen
- [ ] Toast sukses muncul dan auto-dismiss setelah 4 detik
- [ ] Toast error muncul dengan warna merah
- [ ] Modal konfirmasi muncul saat tombol trigger diklik
- [ ] Klik Batal → modal tutup tanpa aksi
- [ ] Klik Lanjutkan → modal tutup (aksi bisa kosong dulu)
- [ ] Stepper menampilkan step dengan highlight yang benar
- [ ] Button menampilkan spinner saat `loading = true`
- [ ] Semua komponen tampil baik di mobile (375px) dan desktop

---

## M05 — Admin: Lokasi & Lot

### Tasks
- [x] Buat `Admin\LocationController`
- [x] Buat `UploadLocationRequest` — validasi file excel (mimes: xlsx,xls, max: 5MB)
- [x] Buat `LocationImportService`:
  - Baca file Excel baris per baris (skip header baris 1)
  - Kolom A = Lokasi, B = Zona, C = Nomor Lot
  - `firstOrCreate` Location → Zone → Lot
  - Kumpulkan error per baris jika data tidak valid
  - Return: jumlah berhasil diimport + list error
- [x] Buat view `admin/locations/index.blade.php`:
  - Tombol "Upload File Excel" → form upload (tampil inline atau modal)
  - Preview hasil import setelah upload (jumlah data, list error jika ada)
  - Modal konfirmasi sebelum simpan
  - Toast sukses/gagal setelah simpan
  - List data: accordion Lokasi → Zona → list Lot
  - Tombol hapus per Lokasi (cascade) + modal konfirmasi
- [x] Daftarkan routes:
  - `GET /admin/locations`
  - `POST /admin/locations/upload`
  - `DELETE /admin/locations/{id}`

### Test
- [ ] Buat file Excel contoh dengan 3 lokasi, masing-masing 2 zona, masing-masing 3 lot
- [ ] Upload file → preview tampil dengan benar (jumlah data)
- [ ] Klik simpan → data tersimpan di DB
- [ ] Cek tabel `locations`, `zones`, `lots` — data masuk
- [ ] Upload file yang sama lagi → tidak duplikat (idempotent)
- [ ] Upload file dengan kolom salah → tampil pesan error yang jelas
- [ ] Accordion tampil: bisa expand/collapse per lokasi
- [ ] Hapus lokasi → modal konfirmasi muncul
- [ ] Konfirmasi hapus → lokasi + zona + lot terkait terhapus dari DB
- [ ] Toast sukses muncul setelah hapus

---

## M06 — Admin: Time Slots

### Tasks
- [x] Buat `Admin\TimeSlotController` (index, store, update, destroy)
- [x] Buat view `admin/time-slots/index.blade.php`:
  - Tabel list slot jam (start_time, end_time, status aktif)
  - Form generate slot: input jam mulai & jam selesai, lalu generate slot per 1 jam (hitung di backend)
  - Toggle aktif/nonaktif per slot
  - Tombol hapus per slot + modal konfirmasi
  - Toast sukses/gagal setiap aksi
- [x] Validasi: tidak boleh ada 2 slot dengan start_time sama
- [x] Daftarkan routes:
  - `GET /admin/time-slots`
  - `POST /admin/time-slots`
  - `PATCH /admin/time-slots/{id}`
  - `DELETE /admin/time-slots/{id}`

### Test
- [ ] Tambah slot jam `08:00` → tersimpan, end_time otomatis `09:00`
- [ ] Tambah slot jam `08:00` lagi → error duplikat
- [ ] Tambah beberapa slot: `08:00`, `09:00`, `10:00`, `13:00`, `14:00`
- [ ] Toggle nonaktif slot `14:00` → status berubah
- [ ] Hapus slot + konfirmasi → slot terhapus
- [ ] Toast muncul di setiap aksi

---

## M07 — Public Booking Form

### Tasks
- [x] Buat Livewire component `BookingForm` (`php artisan make:livewire BookingForm`)
- [x] Implementasi state management stepper (current step 1–4)
- [x] **Step 1 — Pilih Lokasi:**
  - Fetch semua lokasi aktif
  - Tampilkan sebagai card/button grid
  - Pilih lokasi → highlight, aktifkan tombol Lanjut
- [x] **Step 2 — Pilih Zona & Lot:**
  - Dropdown zona berdasarkan lokasi terpilih (reactive Livewire)
  - Date picker (tidak bisa pilih tanggal lampau)
  - Dropdown time slot aktif
  - Setelah zona + tanggal + jam dipilih → tampilkan grid lot tersedia
  - Lot yang sudah terisi (confirmed) disembunyikan
  - Pilih lot → highlight
- [x] **Step 3 — Fasilitas:**
  - Number input tenda (min 1, max 2, default 1)
  - Number input kursi (min 5, max 10, default 5)
  - Number input tong bakar (min 0, max 2, default 0)
  - Toggle meja sembayang (Ya/Tidak)
  - Toggle lampu (Ya/Tidak)
  - Tampilkan aturan min/max di bawah setiap field
- [x] **Step 4 — Data Diri & Konfirmasi:**
  - Input nama lengkap
  - Input email
  - Input nomor HP
  - Ringkasan booking (lokasi, zona, lot, tanggal, jam, fasilitas)
  - Tombol "Konfirmasi & Kirim Booking" + modal konfirmasi
- [x] Buat `StoreBookingRequest` — validasi semua field
- [x] Implementasi `store()` di Livewire atau controller:
  - Cek ketersediaan lot dengan `lockForUpdate()` dalam transaction
  - Generate booking code `ZR-YYYYMMDD-XXXX`
  - Simpan `Booking` + `BookingFacility` dalam DB transaction
  - Handle `QueryException` untuk unique constraint violation
- [x] Buat view `public/booking/success.blade.php`:
  - Kode booking ditampilkan besar
  - Semua info booking
  - Pesan "Simpan kode booking Anda"
- [x] Daftarkan routes public

### Test
- [ ] Akses `/` → form booking tampil
- [ ] Step 1: klik lokasi → terhighlight, tombol Lanjut aktif
- [ ] Step 2: pilih zona → dropdown lot area tampil
- [ ] Step 2: pilih tanggal lampau → tidak bisa dipilih
- [ ] Step 2: pilih zona + tanggal + jam → grid lot tampil
- [ ] Step 3: isi tenda = 0 → error validasi
- [ ] Step 3: isi kursi = 3 → error validasi
- [ ] Step 4: isi semua data → ringkasan tampil dengan benar
- [ ] Submit → modal konfirmasi muncul
- [ ] Konfirmasi → booking tersimpan, redirect ke halaman sukses
- [ ] Halaman sukses: kode booking tampil, semua info benar
- [ ] Coba booking lot yang sama di tanggal & jam yang sama → error "Lot sudah dipesan"
- [ ] Tampilan mobile (375px) semua step nyaman digunakan

---

## M08 — Admin & PIC: Dashboard

### Tasks
- [x] Buat `Admin\DashboardController@index` dengan filter:
  - Tanggal booking (date range)
  - Lokasi (dropdown)
  - Zona (dropdown, muncul setelah lokasi dipilih)
  - Lot (dropdown, muncul setelah zona dipilih)
  - Status (Confirmed / Cancelled)
- [x] Buat view `admin/dashboard/index.blade.php`:
  - Form filter di atas
  - Tabel booking: kode, nama, lokasi/zona/lot, tanggal & jam, fasilitas (ringkasan), status (chip warna), tombol Detail
  - Pagination
- [x] Buat `Admin\BookingController@show` — tampil semua data booking
- [x] Buat view `admin/bookings/show.blade.php`:
  - Semua data booking + fasilitas
  - Tombol "Batalkan Booking" (merah) + modal konfirmasi
- [x] Implementasi `Admin\BookingController@cancel`:
  - Update status → `cancelled`
  - Simpan `cancelled_by` dan `cancelled_at`
  - Return dengan toast sukses
- [x] Buat `Pic\DashboardController@index` — identik admin, tanpa aksi
- [x] Buat view `pic/dashboard/index.blade.php` — tanpa tombol aksi
- [x] Buat `Pic\BookingController@show` — identik admin, tanpa tombol cancel
- [x] Buat view `pic/bookings/show.blade.php`
- [x] Tambah menu navigasi di sidebar admin & pic

### Test
- [ ] Admin dashboard: semua booking dari M07 tampil
- [ ] Filter by lokasi → hasil terfilter
- [ ] Filter by tanggal range → hasil terfilter
- [ ] Filter by status cancelled → tampil booking yang dicancel
- [ ] Klik Detail → halaman detail tampil lengkap
- [ ] Klik Batalkan → modal konfirmasi muncul
- [ ] Konfirmasi cancel → status berubah ke Cancelled, chip merah
- [ ] Toast sukses muncul setelah cancel
- [ ] Login sebagai PIC → dashboard tampil tapi tanpa tombol cancel
- [ ] PIC akses detail booking → tidak ada tombol batalkan

---

## M09 — Admin: Settings Discord

### Tasks
- [x] Buat `Admin\SettingController@index` dan `@update`
- [x] Buat view `admin/settings/index.blade.php`:
  - Input Discord Webhook URL
  - Time picker jam kirim 1
  - Time picker jam kirim 2
  - Info teks: penjelasan rentang data per jam kirim
  - Tombol Simpan + modal konfirmasi + toast sukses/gagal
- [x] Validasi: webhook URL harus format URL valid, kedua jam tidak boleh sama
- [x] Daftarkan routes

### Test
- [ ] Halaman settings tampil dengan data current dari DB
- [ ] Simpan webhook URL valid → tersimpan, toast sukses
- [ ] Simpan webhook URL tidak valid → error validasi
- [ ] Ubah jam kirim 1 dan 2 → tersimpan di DB
- [ ] Cek tabel `discord_settings` — data terupdate

---

## M10 — Discord Notification

### Tasks
- [x] Buat `BookingsExport` class (implements `FromCollection`, `WithHeadings`)
  - Kolom: Kode Booking, Tanggal Ziarah, Jam, Nama, Email, No. HP, Lokasi, Zona, No. Lot, Tenda, Kursi, Tong Bakar, Meja Sembayang, Lampu, Status
- [x] Buat `ExcelExportService`:
  - Method `generate(Collection $bookings): string` → return path file temp `.xlsx`
- [x] Buat `DiscordService`:
  - Method `send(string $message, string $filePath): void`
  - Kirim multipart webhook ke Discord (message + file attachment)
  - Log error jika gagal, tidak throw exception
- [x] Buat `SendDiscordNotificationJob`:
  - Terima parameter `send_time_key` ('send_time_1' atau 'send_time_2')
  - Tentukan rentang waktu:
    - `send_time_1`: 14:01 kemarin s/d 07:59 hari ini
    - `send_time_2`: 08:01 hari ini s/d 13:59 hari ini
  - Query bookings dalam rentang (join ke time_slots, filter by booking_date + start_time)
  - Jika tidak ada booking → kirim pesan "Tidak ada booking pada periode ini" (tanpa file)
  - Generate Excel via `ExcelExportService`
  - Buat pesan summary (total booking, total per fasilitas)
  - Kirim via `DiscordService`
  - Hapus file temp setelah terkirim
- [x] Buat `CheckAndSendDiscordJob` (berjalan setiap menit):
  - Ambil settings dari DB
  - Cek apakah `now()->format('H:i')` cocok dengan `send_time_1` atau `send_time_2`
  - Jika cocok → dispatch `SendDiscordNotificationJob`
- [x] Daftarkan di `Console/Kernel.php`:
  ```php
  $schedule->job(new CheckAndSendDiscordJob())->everyMinute();
  ```

### Test
- [ ] Pastikan ada data booking di DB (dari M07)
- [ ] Isi webhook URL valid di settings (M09)
- [ ] Test manual via tinker:
  ```php
  dispatch(new SendDiscordNotificationJob('send_time_2'));
  ```
- [ ] Cek channel Discord → pesan summary terkirim
- [ ] Cek channel Discord → file Excel ter-attach
- [ ] Buka Excel → kolom dan data sesuai spesifikasi
- [ ] Test dengan 0 booking → pesan "Tidak ada booking" terkirim tanpa file
- [ ] Test webhook URL salah → error ter-log di `storage/logs/laravel.log`, aplikasi tidak crash
- [ ] Jalankan `php artisan schedule:work`, tunggu menit yang cocok dengan jam setting → notifikasi terkirim otomatis
