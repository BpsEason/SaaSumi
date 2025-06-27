<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AIProxyService;
use App\Modules\Booking\BookingServiceProvider;
use App\Modules\LineNotify\LineNotifyServiceProvider;
use App\Modules\Billing\BillingServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register core services
        $this->app->singleton(AIProxyService::class, function ($app) {
            return new AIProxyService();
        });

        // Register modules
        $this->app->register(BookingServiceProvider::class);
        $this->app->register(LineNotifyServiceProvider::class);
        // Conditionally register other modules
        // For demonstration, assume Billing module is enabled
        $this->app->register(BillingServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
