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

   * Apakah user sudah pernah booking jadwal ini
   * Apakah slot masih tersedia
3. Data jadwal dikunci menggunakan database transaction (`lockForUpdate`) untuk mencegah race condition
4. Jika semua kondisi terpenuhi:

   * Data booking dibuat
5. Response dikirim dengan data terbaru (termasuk slot yang sudah terpakai)

---

## Aturan 

Beberapa aturan yang diterapkan dalam sistem:

* User tidak bisa booking jadwal yang sama lebih dari satu kali
* Slot tidak boleh melebihi kapasitas yang ditentukan
* Booking yang dibatalkan akan mengembalikan slot
* Sistem aman dari race condition dengan penggunaan transaksi database

---

## Pendekatan Arsitektur

Project ini menggunakan pendekatan **service-layer**, dengan pembagian:

* **Controller**: menangani request dan response
* **Form Request**: validasi input
* **Service**: seluruh logika bisnis
* **Model**: interaksi database
* **Resource**: format output API

Pendekatan ini membuat kode lebih terstruktur, mudah dirawat, dan mudah dikembangkan.

---

## Teknologi yang Digunakan

* Laravel (PHP) sebagai backend framework
* MySQL sebagai database
* Laravel Sanctum untuk autentikasi berbasis token
* REST API dengan format JSON

---

## Penggunaan AI dalam Pengembangan

Dalam proses pembuatan project ini, saya menggunakan bantuan AI seperti ChatGPT dan Claude sebagai alat bantu.

AI digunakan untuk:

* Mengeksplorasi pendekatan struktur kode
* Memberikan referensi terkait best practice
* Membantu memahami edge case dalam logika booking

Namun, project ini tidak sepenuhnya dibuat oleh AI:

* Sebagian logika ditulis dan disesuaikan sendiri
* Proses debugging dan penyesuaian dilakukan secara mandiri
* AI digunakan sebagai pendukung untuk mempercepat proses belajar, bukan sebagai pengganti pemahaman

Pendekatan ini membantu saya tetap memahami sistem yang dibangun secara menyeluruh.

---

## Cara Pengujian

API dapat diuji menggunakan Postman atau tools serupa:

1. Ambil daftar jadwal

   ```
   GET /api/schedules
   ```

2. Gunakan token autentikasi (Sanctum)

3. Lakukan booking

   ```
   POST /api/bookings
   ```

4. Coba booking dua kali untuk jadwal yang sama
   Sistem akan menolak request

5. Batalkan booking
   Slot akan kembali tersedia

---

## Hal yang Dipelajari

Dari project ini, saya belajar:

* Menyusun backend dengan struktur yang lebih rapi
* Mengimplementasikan logika bisnis di luar controller
* Menangani masalah nyata seperti double booking
* Menggunakan transaksi database untuk menjaga konsistensi data
* Memanfaatkan AI sebagai alat bantu tanpa bergantung sepenuhnya