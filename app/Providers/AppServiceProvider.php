<?php

namespace App\Providers;

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
        // 1. Konfigurasi Pagination Bootstrap
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // 2. Audit SQL untuk membuktikan Eager Loading
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            \Illuminate\Support\Facades\Log::info(sprintf(
                "[SQL AUDIT] (%.2f ms) %s", $query->time, $query->sql
            ));
        });
    }
}