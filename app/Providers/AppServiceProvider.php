<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * daftar layanan aplikasi
     */
    public function register(): void
    {
        // tempat untuk mendaftarkan service atau binding jika dibutuhkan
    }

    /**
     * inisialisasi layanan aplikasi saat boot
     */
    public function boot(): void
    {
        // tempat inisialisasi: event, observer, atau pengecekan hak akses (policy/gate) kalau perlu
    }
}