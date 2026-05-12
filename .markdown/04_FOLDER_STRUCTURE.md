# Struktur Folder Laravel

```
app/
├── Console/
│   └── Kernel.php                          # Scheduler definition
├── Exceptions/
│   └── Handler.php
├── Http/
│   ├── Controllers/
│   │   ├── BookingController.php           # Public booking form
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── BookingController.php
│   │   │   ├── LocationController.php
│   │   │   ├── TimeSlotController.php
│   │   │   └── SettingController.php
│   │   └── Pic/
│   │       ├── DashboardController.php
│   │       └── BookingController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php              # Cek role admin/pic
│   └── Requests/
│       ├── StoreBookingRequest.php
│       ├── UploadLocationRequest.php
│       └── UpdateSettingRequest.php
├── Jobs/
│   └── SendDiscordNotificationJob.php      # Dispatch notifikasi Discord
├── Models/
│   ├── User.php
│   ├── Location.php
│   ├── Zone.php
│   ├── Lot.php
│   ├── TimeSlot.php
│   ├── Booking.php
│   ├── BookingFacility.php
│   └── DiscordSetting.php
├── Services/
│   ├── DiscordService.php                  # Kirim webhook + file Excel
│   ├── ExcelExportService.php              # Generate Excel booking
│   └── LocationImportService.php          # Parse & import Excel lokasi
└── Exports/
    └── BookingsExport.php                  # Maatwebsite export class

database/
├── migrations/
│   ├── xxxx_create_users_table.php
│   ├── xxxx_create_locations_table.php
│   ├── xxxx_create_zones_table.php
│   ├── xxxx_create_lots_table.php
│   ├── xxxx_create_time_slots_table.php
│   ├── xxxx_create_bookings_table.php
│   ├── xxxx_create_booking_facilities_table.php
│   └── xxxx_create_discord_settings_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── AdminUserSeeder.php                 # Seed user admin default

resources/
├── views/
│   ├── layouts/
│   │   ├── public.blade.php                # Layout form publik
│   │   ├── admin.blade.php                 # Layout dashboard admin
│   │   └── pic.blade.php                   # Layout dashboard PIC
│   ├── public/
│   │   ├── booking/
│   │   │   ├── index.blade.php             # Form booking (stepper)
│   │   │   └── success.blade.php           # Halaman sukses
│   ├── admin/
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── bookings/
│   │   │   └── show.blade.php
│   │   ├── locations/
│   │   │   └── index.blade.php
│   │   ├── time-slots/
│   │   │   └── index.blade.php
│   │   └── settings/
│   │       └── index.blade.php
│   ├── pic/
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   └── bookings/
│   │       └── show.blade.php
│   └── auth/
│       └── login.blade.php
└── css/
    └── app.css                             # Tailwind entry point

routes/
├── web.php
└── api.php                                 # AJAX endpoints (zones, lots, time-slots)

config/
└── discord.php                             # Config webhook (fallback dari DB)
```
