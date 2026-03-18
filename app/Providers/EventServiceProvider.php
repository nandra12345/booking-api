<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * pemetaan event ke listener untuk aplikasi
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * daftarkan event untuk aplikasi
     */
    public function boot(): void
    {
        // tempat untuk mendaftarkan event listener tambahan atau melakukan binding event
    }

    /**
     * tentukan apakah event dan listener bisa otomatis ditemukan
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}