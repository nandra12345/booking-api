<?php

namespace App\Providers;

use App\Services\BookingService;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * daftar layanan
     */
    public function register(): void
    {
        // daftarkan booking service sebagai singleton
        $this->app->singleton(BookingService::class);
    }

    /**
     * inisialisasi layanan saat boot
     */
    public function boot(): void
    {
        // tempat inisialisasi atau binding tambahan jika diperlukan
    }
}