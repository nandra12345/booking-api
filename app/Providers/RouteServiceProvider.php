<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * path untuk route "home" aplikasi
     *
     * biasanya user diarahkan ke sini setelah login
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * atur binding model route, pattern filter, dan konfigurasi route lainnya
     */
    public function boot(): void
    {
        // batasi request untuk grup 'api': 60 request per menit per user atau per ip
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // daftarkan route api dengan middleware 'api' dan prefix 'api'
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // daftarkan route web dengan middleware 'web'
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}