# Routes & Halaman

## Public Routes (tanpa auth)

| Method | URI | Controller | Keterangan |
|--------|-----|------------|------------|
| GET | `/` | `BookingController@index` | Form booking |
| GET | `/api/zones/{location_id}` | `BookingController@getZones` | AJAX cascading |
| GET | `/api/lots/{zone_id}` | `BookingController@getLots` | AJAX cascading (filter lot tersedia) |
| GET | `/api/lots/available` | `BookingController@getAvailableLots` | Cek lot tersedia by date+time_slot |
| GET | `/api/time-slots` | `BookingController@getTimeSlots` | Ambil semua slot aktif |
| POST | `/booking` | `BookingController@store` | Submit booking |
| GET | `/booking/success/{code}` | `BookingController@success` | Halaman sukses booking |

---

## Auth Routes

| Method | URI | Keterangan |
|--------|-----|------------|
| GET | `/login` | Halaman login (Admin & PIC) |
| POST | `/login` | Proses login |
| POST | `/logout` | Logout |

---

## Admin Routes (middleware: auth, role:admin)

| Method | URI | Controller | Keterangan |
|--------|-----|------------|------------|
| GET | `/admin/dashboard` | `Admin\DashboardController@index` | List booking + filter |
| GET | `/admin/bookings/{id}` | `Admin\BookingController@show` | Detail booking |
| PATCH | `/admin/bookings/{id}/cancel` | `Admin\BookingController@cancel` | Cancel booking |
| GET | `/admin/locations` | `Admin\LocationController@index` | List lokasi/zona/lot |
| POST | `/admin/locations/upload` | `Admin\LocationController@upload` | Upload Excel |
| DELETE | `/admin/locations/{id}` | `Admin\LocationController@destroy` | Hapus lokasi + cascade |
| GET | `/admin/time-slots` | `Admin\TimeSlotController@index` | List slot jam |
| POST | `/admin/time-slots` | `Admin\TimeSlotController@store` | Tambah slot |
| PATCH | `/admin/time-slots/{id}` | `Admin\TimeSlotController@update` | Edit slot |
| DELETE | `/admin/time-slots/{id}` | `Admin\TimeSlotController@destroy` | Hapus slot |
| GET | `/admin/settings` | `Admin\SettingController@index` | Halaman settings Discord |
| PATCH | `/admin/settings` | `Admin\SettingController@update` | Simpan settings |

---

## PIC Routes (middleware: auth, role:pic)

| Method | URI | Controller | Keterangan |
|--------|-----|------------|------------|
| GET | `/pic/dashboard` | `Pic\DashboardController@index` | List booking read-only + filter |
| GET | `/pic/bookings/{id}` | `Pic\BookingController@show` | Detail booking read-only |

---

## Redirect Setelah Login
- Admin → `/admin/dashboard`
- PIC → `/pic/dashboard`
