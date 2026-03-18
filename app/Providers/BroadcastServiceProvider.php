<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * bootstrap layanan aplikasi untuk broadcasting
     */
    public function boot(): void
    {
        // daftarkan route untuk broadcasting (pusher, redis, dll)
        Broadcast::routes();

        // muat definisi channel dari routes/channels.php
        require base_path('routes/channels.php');
    }
}