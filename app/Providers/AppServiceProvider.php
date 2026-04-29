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
        \App\Models\Pelanggan::observe(\App\Observers\PelangganObserver::class);
        \App\Models\PencatatanMeter::observe(\App\Observers\PencatatanMeterObserver::class);
        \App\Models\Pembayaran::observe(\App\Observers\PembayaranObserver::class);
    }
}
