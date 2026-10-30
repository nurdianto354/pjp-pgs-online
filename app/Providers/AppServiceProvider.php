<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Daftarkan subfolder migrasi modul kelompok agar otomatis terbaca saat php artisan migrate
        if (is_dir(database_path('migrations/kelompok/masterData'))) {
            $this->loadMigrationsFrom(database_path('migrations/kelompok/masterData'));
        }

        // Paksa skema HTTPS pada production, reverse proxy, atau domain live
        $host = request()->header('host') ?? '';
        if (app()->environment('production') || str_contains($host, 'pgsdua.web.id') || str_contains($host, 'loca.lt') || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
