# Database Schema

## Diagram Relasi
```
locations
  └── zones
        └── lots
              └── bookings
                    └── booking_facilities

users (admin/pic)
time_slots (global)
discord_settings
```

---

## Tabel: `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(255) | |
| email | varchar(255) unique | |
| password | varchar(255) | hashed |
| role | enum('admin','pic') | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel: `locations`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(255) | Nama lokasi |
| is_active | boolean default true | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel: `zones`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| location_id | bigint FK → locations.id | |
| name | varchar(255) | Nama zona |
| is_active | boolean default true | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel: `lots`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| zone_id | bigint FK → zones.id | |
| number | varchar(50) | Nomor lot |
| is_active | boolean default true | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel: `time_slots`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| start_time | time | Contoh: 08:00 |
| end_time | time | Contoh: 09:00 (selalu +1 jam) |
| is_active | boolean default true | |
| created_at | timestamp | |
| updated_at | timestamp | |

> Slot jam bersifat global, berlaku untuk semua lokasi.

---

## Tabel: `bookings`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| booking_code | varchar(20) unique | Format: ZR-YYYYMMDD-XXXX |
| name | varchar(255) | Nama pemesan |
| email | varchar(255) | |
| phone | varchar(20) | |
| lot_id | bigint FK → lots.id | |
| time_slot_id | bigint FK → time_slots.id | |
| booking_date | date | Tanggal ziarah |
| status | enum('confirmed','cancelled') default 'confirmed' | |
| cancelled_by | bigint FK → users.id nullable | Admin yang cancel |
| cancelled_at | timestamp nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |

> **Unique constraint:** `(lot_id, time_slot_id, booking_date)` — 1 lot tidak bisa dipesan 2x di tanggal & jam yang sama.

---

## Tabel: `booking_facilities`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| booking_id | bigint FK → bookings.id | |
| tent_count | smallint | Min 1, Max 2 |
| chair_count | smallint | Min 5, Max 10 |
| burn_barrel_count | smallint | Min 0, Max 2 |
| prayer_table | boolean | Meja sembayang |
| lamp | boolean | Lampu |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Tabel: `discord_settings`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| webhook_url | text | Discord webhook URL |
| send_time_1 | time default '08:00' | Jam kirim pertama |
| send_time_2 | time default '14:00' | Jam kirim kedua |
| updated_by | bigint FK → users.id | |
| updated_at | timestamp | |

---

## Migrations Order
1. `create_users_table`
2. `create_locations_table`
3. `create_zones_table`
4. `create_lots_table`
5. `create_time_slots_table`
6. `create_bookings_table`
7. `create_booking_facilities_table`
8. `create_discord_settings_table`
