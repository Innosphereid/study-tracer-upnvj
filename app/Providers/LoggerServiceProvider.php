<?php

namespace App\Providers;

use App\Contracts\Services\ActivityLoggerInterface;
use App\Services\ActivityLoggerService;
use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityLoggerInterface::class, ActivityLoggerService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}