<?php

namespace App\Modules\Billing;

use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
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
        // Load migrations, routes, etc. for Billing module
        // $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        // $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
