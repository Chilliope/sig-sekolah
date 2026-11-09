<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Sekolah;
use App\Observers\SekolahObserver;

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
        Sekolah::observe(SekolahObserver::class);
    }
}
