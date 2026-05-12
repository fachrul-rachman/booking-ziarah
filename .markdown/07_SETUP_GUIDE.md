# Setup & Installation Guide

## Prerequisites
- PHP >= 8.2
- Composer
- PostgreSQL >= 14
- Node.js >= 18 + npm
- Laravel 11

---

## Langkah Instalasi

### 1. Clone & Install Dependencies
```bash
git clone <repo-url> ziarah-booking
cd ziarah-booking

composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Booking Ziarah"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ziarah_booking
DB_USERNAME=your_pg_user
DB_PASSWORD=your_pg_password
```

### 3. Database
```bash
# Buat database di PostgreSQL terlebih dahulu
createdb ziarah_booking

# Jalankan migration
php artisan migrate

# Seed admin user default
php artisan db:seed --class=AdminUserSeeder
```

Default admin credentials (ganti setelah login pertama):
- Email: `admin@ziarah.local`
- Password: `Admin@12345`

### 4. Build Assets
```bash
npm run build
# atau untuk development:
npm run dev
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Install Laravel Excel
```bash
composer require maatwebsite/excel
```

---

## Menjalankan Scheduler (Development)
```bash
php artisan schedule:work
```

Untuk production, tambahkan cron job:
```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Menjalankan Aplikasi (Development)
```bash
php artisan serve
# Akses di: http://localhost:8000
```

---

## Format File Excel Upload Lokasi

Buat file `.xlsx` dengan struktur:

| Lokasi | Zona | Nomor Lot |
|--------|------|-----------|
| Pemakaman Barat | Blok A | A-001 |
| Pemakaman Barat | Blok A | A-002 |
| Pemakaman Timur | Blok B | B-001 |

- Baris pertama adalah header (akan dilewati otomatis)
- Nama kolom bebas, yang penting urutannya: **Kolom A = Lokasi, Kolom B = Zona, Kolom C = Nomor Lot**
- File diupload melalui menu **Admin → Kelola Lokasi → Upload File Excel**

---

## Konfigurasi Discord

1. Buat Webhook di Discord Server:
   - Server Settings → Integrations → Webhooks → New Webhook
   - Copy Webhook URL

2. Masuk ke aplikasi sebagai Admin → **Pengaturan**
3. Tempel Webhook URL
4. Atur jam pengiriman notifikasi
5. Klik Simpan

---

## Package yang Digunakan

| Package | Kegunaan |
|---------|---------|
| `laravel/breeze` | Auth (login/logout) |
| `maatwebsite/excel` | Import & export Excel |
| `guzzlehttp/guzzle` | HTTP client untuk Discord webhook |
| `tailwindcss` | Styling (via npm) |

---

## Notes Tambahan
- Scheduler wajib berjalan agar notifikasi Discord terkirim otomatis
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh web server
- Untuk production, gunakan queue driver (database/redis) jika ingin job berjalan async:
  ```env
  QUEUE_CONNECTION=database
  ```
  Lalu jalankan: `php artisan queue:work`
