<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    $link = public_path('storage');

    if (! file_exists($link) && ! is_link($link)) {
        try {
            symlink(storage_path('app/public'), $link);
        } catch (\Throwable $e) {
            // Si el servidor no permite enlaces, la app sigue funcionando
        }
    }
}
}
