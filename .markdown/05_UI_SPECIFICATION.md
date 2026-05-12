# Spesifikasi UI & Halaman

## Design System

### Warna
| Fungsi | Warna | Tailwind Class |
|--------|-------|----------------|
| Primary (CTA utama) | Biru tua | `bg-blue-700` |
| Sukses / Confirmed | Hijau | `bg-green-600` |
| Bahaya / Cancel | Merah | `bg-red-600` |
| Peringatan | Kuning | `bg-yellow-500` |
| Disabled | Abu-abu | `bg-gray-300` |
| Background halaman | Putih / Abu terang | `bg-gray-50` |

### Typography
- Font: `font-sans` (sistem)
- Body minimum: `text-base` (16px)
- Label form: `text-lg font-semibold`
- Heading: `text-2xl font-bold`

### Komponen Global
- **Toast Notification:** muncul di pojok atas kanan, auto-dismiss 4 detik, warna sesuai status (hijau sukses, merah gagal)
- **Modal Konfirmasi:** setiap aksi destruktif (cancel booking, hapus data) wajib tampilkan modal konfirmasi dulu
- **Loading State:** tombol submit menampilkan spinner + disabled saat proses berlangsung

---

## Halaman: Form Booking Publik (`/`)

### Stepper (4 langkah)
```
[1. Pilih Lokasi] → [2. Pilih Zona & Lot] → [3. Fasilitas & Jadwal] → [4. Data Diri]
```

**Step 1 — Pilih Lokasi**
- Tampilkan card/button per lokasi
- Setelah pilih, highlight lokasi terpilih, tombol "Lanjut" aktif

**Step 2 — Pilih Zona & Lot**
- Dropdown zona (diisi setelah lokasi dipilih via AJAX)
- Setelah zona dipilih, tampilkan grid nomor lot yang tersedia (lot sudah terisi di tanggal+jam terpilih disembunyikan)
- Catatan: user harus pilih tanggal & jam dulu sebelum lot ditampilkan (tampilkan pesan panduan jika belum)

**Step 3 — Fasilitas & Jadwal**
- **Tanggal:** date picker (tidak bisa pilih tanggal lampau)
- **Jam:** dropdown time slot aktif
- **Tenda:** number input (min 1, max 2) — tampilkan aturan di bawah field
- **Kursi:** number input (min 5, max 10)
- **Tong Bakar:** number input (min 0, max 2)
- **Meja Sembayang:** toggle Ya/Tidak
- **Lampu:** toggle Ya/Tidak

**Step 4 — Data Diri**
- Nama lengkap (required)
- Email (required, format email)
- Nomor HP (required, format angka)
- Ringkasan booking (lokasi, zona, lot, tanggal, jam, fasilitas)
- Tombol "Konfirmasi & Kirim Booking"
- Modal konfirmasi sebelum submit

**Halaman Sukses (`/booking/success/{code}`)**
- Kode booking ditampilkan besar
- Info: nama, lokasi, zona, lot, tanggal, jam, fasilitas
- Pesan: "Simpan kode booking Anda"
- Tombol: Cetak / Kembali ke Beranda

---

## Halaman: Login (`/login`)
- Form email + password
- Tombol "Masuk"
- Tidak ada fitur register publik

---

## Halaman: Admin Dashboard (`/admin/dashboard`)

### Filter
- Tanggal booking (date range picker)
- Lokasi (dropdown)
- Zona (dropdown, muncul setelah lokasi dipilih)
- Lot (dropdown, muncul setelah zona dipilih)
- Status (Confirmed / Cancelled)

### Tabel Booking
| Kolom | Keterangan |
|-------|------------|
| Kode Booking | |
| Nama | |
| Lokasi / Zona / Lot | |
| Tanggal & Jam | |
| Fasilitas | Ringkasan singkat |
| Status | Chip warna (hijau/merah) |
| Aksi | Tombol Detail |

### Detail Booking (`/admin/bookings/{id}`)
- Semua data booking ditampilkan
- Tombol "Batalkan Booking" (merah) + modal konfirmasi
- Setelah cancel: status berubah, toast sukses

---

## Halaman: Admin Lokasi (`/admin/locations`)

### Upload Excel
- Tombol "Upload File Excel"
- Format yang diterima: `.xlsx`, `.xls`
- Kolom yang diharapkan: `Lokasi | Zona | Nomor Lot`
- Preview data setelah upload sebelum disimpan
- Konfirmasi simpan dengan modal
- Toast sukses/gagal

### List Data
- Accordion per Lokasi → Zona → list Lot
- Tombol hapus per lokasi (cascade hapus zona & lot terkait) + modal konfirmasi

---

## Halaman: Admin Time Slots (`/admin/time-slots`)
- List slot jam aktif (tabel)
- Form tambah slot: input jam mulai (time picker), jam selesai otomatis +1 jam
- Tombol hapus per slot + modal konfirmasi
- Toggle aktif/nonaktif per slot

---

## Halaman: Admin Settings (`/admin/settings`)
- Input: Discord Webhook URL
- Input: Jam kirim 1 (time picker)
- Input: Jam kirim 2 (time picker)
- Tombol "Simpan" + modal konfirmasi + toast sukses/gagal
- Tampilkan info: "Notifikasi berisi data booking dari rentang jam sebelumnya"

---

## Halaman: PIC Dashboard (`/pic/dashboard`)
- Identik dengan Admin Dashboard **tanpa** tombol aksi (tidak ada cancel, tidak ada upload)
- Filter tetap tersedia (read-only browse)

## Halaman: PIC Detail Booking (`/pic/bookings/{id}`)
- Identik dengan Admin detail **tanpa** tombol cancel
