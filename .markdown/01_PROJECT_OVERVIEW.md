# Project Overview — Sistem Booking Ziarah

## Tujuan
Aplikasi web untuk booking ziarah secara publik, dengan dashboard manajemen untuk Admin dan PIC, serta notifikasi otomatis ke Discord.

## Tech Stack
- **Backend:** Laravel 11
- **Database:** PostgreSQL
- **Frontend:** Blade + Tailwind CSS
- **Auth:** Laravel Breeze (Admin & PIC)
- **Excel:** Maatwebsite/Laravel-Excel
- **Scheduler:** Laravel Scheduler (built-in)

## Roles
| Role | Akses |
|------|-------|
| Publik | Form booking (tanpa login) |
| PIC | Dashboard read-only |
| Admin | Full CRUD + settings |

## Design Principles
- **Mobile-first** — mayoritas user menggunakan HP
- **Clean & cerah** — target user berumur, font besar, kontras tinggi
- **Warna informatif** — tombol dan chip menggunakan warna yang bermakna (hijau = berhasil, merah = gagal, kuning = peringatan)
- **Konfirmasi setiap aksi** — semua perubahan (submit, edit, cancel) wajib tampilkan modal konfirmasi + notifikasi sukses/gagal (toast)
- **Bahasa jelas** — hindari jargon teknis di UI publik
