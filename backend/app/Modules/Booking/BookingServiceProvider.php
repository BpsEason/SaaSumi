<?php

namespace App\Modules\Booking;

use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations, routes, etc.
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        // $this->loadRoutesFrom(__DIR__ . '/routes.php'); // Uncomment if Booking module has specific web routes
    }
}
