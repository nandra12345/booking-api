<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * pemetaan model ke policy untuk aplikasi
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * daftarkan layanan otentikasi / otorisasi
     */
    public function boot(): void
    {
        // tempat mendaftarkan policy, gate, atau aturan otorisasi lainnya jika diperlukan
    }
}