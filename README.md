# Booking System — Laravel API

Sistem booking berbasis REST API yang dibangun menggunakan Laravel dengan pendekatan service-layer architecture, Form Request untuk validasi, dan API Resource untuk konsistensi format response.

---

## Alur Sistem

Secara umum, alur request dalam sistem ini adalah sebagai berikut:

```
Client Request
   ↓
Route (api.php)
   ↓
Controller
   ↓
Form Request (validasi)
   ↓
Service (logika bisnis + transaksi database)
   ↓
Model (interaksi database)
   ↓
Resource (format response JSON)
```

---

## Penjelasan Alur Booking

Ketika user melakukan booking:

1. Request masuk dan divalidasi (`schedule_id` wajib ada)
2. Sistem melakukan pengecekan:
   - Apakah user sudah pernah booking jadwal ini
   - Apakah slot masih tersedia
3. Data jadwal dikunci menggunakan database transaction (`lockForUpdate`) untuk mencegah race condition
4. Jika semua kondisi terpenuhi:
   - Data booking dibuat (atau di-restore jika sebelumnya pernah dibatalkan)
5. Response dikirim dengan data terbaru (termasuk slot yang sudah terpakai)

---

## Aturan 

Beberapa aturan yang diterapkan dalam sistem:

- User tidak bisa booking jadwal yang sama lebih dari satu kali
- Slot tidak boleh melebihi kapasitas yang ditentukan
- Booking yang dibatalkan akan mengembalikan slot dan memungkinkan re-booking
- Sistem aman dari race condition dengan penggunaan transaksi database

---

## Pendekatan Arsitektur

Project ini menggunakan pendekatan **service-layer**, dengan pembagian:

| Layer | File | Tanggung Jawab |
|---|---|---|
| **Controller** | `ScheduleController`, `BookingController`, `AuthController` | Menangani request dan response |
| **Form Request** | `StoreBookingRequest`, `StoreScheduleRequest` | Validasi input |
| **Service** | `BookingService` | Seluruh logika bisnis + transaksi DB |
| **Model** | `Schedule`, `Booking` | Interaksi database, relasi, helper |
| **Resource** | `ScheduleResource`, `BookingResource` | Format output API |
| **Exception Handler** | `app/Exceptions/Handler.php` | Unified JSON error responses |

Pendekatan ini membuat kode lebih terstruktur, mudah dirawat, dan mudah dikembangkan.

---

## Teknologi yang Digunakan

- **Laravel (PHP)** sebagai backend framework
- **MySQL** sebagai database
- **Laravel Sanctum** untuk autentikasi berbasis token
- **REST API** dengan format JSON

---

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Copy env dan generate key
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi DB di .env, lalu jalankan migrasi
php artisan migrate

# 4. Publish Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 5. Daftarkan service provider di config/app.php → 'providers'
#    App\Providers\BookingServiceProvider::class,

# 6. Jalankan server
php artisan serve
```

---

## Daftar Endpoint

### Guest (tidak perlu token)

| Method | URI | Deskripsi |
|--------|-----|-----------|
| POST | `/api/auth/register` | Registrasi user baru |
| POST | `/api/auth/login` | Login dan dapatkan token |
| GET | `/api/schedules` | Daftar semua jadwal (paginated) |
| GET | `/api/schedules/{id}` | Detail satu jadwal |

### Authenticated (wajib Bearer Token)

| Method | URI | Deskripsi |
|--------|-----|-----------|
| POST | `/api/auth/logout` | Logout dan revoke token |
| POST | `/api/bookings` | Buat booking |
| GET | `/api/bookings/me` | Daftar booking milik saya |
| DELETE | `/api/bookings/{id}` | Batalkan booking |

---

## Cara Pengujian (Postman)

### Langkah 1 — Jalankan server

```bash
php artisan serve
```

Base URL: `http://127.0.0.1:8000`

---

### Langkah 2 — Register

```
POST /api/auth/register
```

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

✅ Copy nilai `token` dari response.

---

### Langkah 3 — Set Token di Postman

Untuk semua request yang butuh autentikasi, tambahkan header berikut:

```
Authorization : Bearer {token_kamu}
Accept        : application/json
```

---

### Langkah 4 — Cek Jadwal yang Tersedia

```
GET /api/schedules
```

atau cek slot detail jadwal tertentu:

```
GET /api/schedules/1
```

Perhatikan field `slot_capacity` dan `slots_taken` untuk mengetahui slot yang masih tersedia.

---

### Langkah 5 — Buat Booking

```
POST /api/bookings

Body:
{
    "schedule_id": 1
}
```

| Kondisi | Status | Pesan |
|---|---|---|
| Berhasil | `201 Created` | Data booking |
| Sudah pernah booking | `409 Conflict` | "You have already booked this schedule." |
| Slot penuh | `422 Unprocessable` | "No available slots remaining for this schedule." |

> **Catatan:** Jika dapat `422`, cek slot via `GET /api/schedules/{id}`. Jika `slots_taken >= slot_capacity`, slot memang sudah habis. Gunakan schedule lain atau buat schedule baru via tinker.

---

### Langkah 6 — Cek Booking Saya

```
GET /api/bookings/me
```

Lihat `id` booking yang statusnya `confirmed` — gunakan id ini untuk langkah berikutnya.

---

### Langkah 7 — Batalkan Booking

```
DELETE /api/bookings/{id}
```

| Kondisi | Status | Pesan |
|---|---|---|
| Berhasil dibatalkan | `200 OK` | Data booking dengan status `cancelled` |
| Booking tidak ditemukan / sudah dihapus | `404 Not Found` | "Booking not found." |
| Bukan milik user ini | `403 Forbidden` | "You are not allowed to cancel this booking." |

> **Catatan:** Jika dapat `404` saat DELETE, kemungkinan booking sudah pernah dibatalkan sebelumnya. Cek terlebih dahulu via `GET /api/bookings/me`.

---

### Langkah 8 — Re-booking Setelah Cancel (Test Soft Delete Fix)

Setelah berhasil cancel, coba booking jadwal yang sama lagi:

```
POST /api/bookings

Body:
{
    "schedule_id": 1
}
```

✅ Harusnya `201 Created` — sistem akan me-restore booking lama daripada membuat row baru, sehingga tidak terjadi duplicate entry pada unique constraint.

---

### Skenario Pengujian 

| Skenario | Langkah | Expected |
|---|---|---|
| Double booking | POST booking → POST booking lagi | `409 Conflict` |
| Slot penuh | Booking hingga kapasitas penuh → POST lagi | `422 Unprocessable` |
| Re-book setelah cancel | DELETE → POST ulang | `201 Created` (restore) |
| Akses tanpa token | Request ke endpoint auth tanpa header | `401 Unauthenticated` |

---

## Buat Schedule via Tinker (untuk Testing)

Jika tidak ada schedule atau slot sudah penuh, buat baru lewat tinker:

```bash
php artisan tinker
```

```php
App\Models\Schedule::create([
    'title'         => 'Test Schedule',
    'start_time'    => '2025-06-01 08:00:00',
    'end_time'      => '2025-06-01 10:00:00',
    'slot_capacity' => 5,
]);
```

---

## Penggunaan AI dalam Pengembangan

Dalam proses pembuatan project ini, saya menggunakan bantuan AI (ChatGPT dan Claude) sebagai alat bantu.

AI digunakan untuk:
- Mengeksplorasi pendekatan struktur kode
- Memberikan referensi terkait best practice
- Membantu memahami edge case dalam logika booking

Namun, project ini tidak sepenuhnya dibuat oleh AI:
- Sebagian logika ditulis dan disesuaikan sendiri
- Proses debugging dan penyesuaian dilakukan secara mandiri
- AI digunakan sebagai pendukung untuk mempercepat proses belajar, bukan sebagai pengganti pemahaman

Pendekatan ini membantu saya tetap memahami sistem yang dibangun secara menyeluruh.

---

## Hal yang Dipelajari

Dari project ini, saya belajar:

- Menyusun backend dengan struktur yang lebih rapi
- Mengimplementasikan logika bisnis di luar controller
- Menangani masalah nyata seperti double booking dan soft delete trap
- Menggunakan transaksi database untuk menjaga konsistensi data
- Memanfaatkan AI sebagai alat bantu tanpa bergantung sepenuhnya

---

## File Map

```
app/
├── Exceptions/
│   └── Handler.php                          ← Unified JSON error responses
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php               ← POST /api/auth/*
│   │   ├── ScheduleController.php           ← GET /api/schedules
│   │   └── BookingController.php            ← POST|GET|DELETE /api/bookings
│   ├── Requests/
│   │   ├── StoreScheduleRequest.php         ← Validasi pembuatan jadwal
│   │   └── StoreBookingRequest.php          ← Validasi pembuatan booking
│   └── Resources/
│       ├── ScheduleResource.php             ← Format JSON jadwal
│       └── BookingResource.php              ← Format JSON booking
├── Models/
│   ├── Schedule.php
│   └── Booking.php
├── Providers/
│   └── BookingServiceProvider.php           ← Singleton binding
└── Services/
    └── BookingService.php                   ← Core business logic

database/migrations/
├── …_create_schedules_table.php
└── …_create_bookings_table.php

routes/
└── api.php
```
