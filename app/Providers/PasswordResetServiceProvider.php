<?php

namespace App\Providers;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Services\PasswordResetServiceInterface;
use App\Repositories\PasswordResetRepository;
use App\Services\PasswordResetService;
use Illuminate\Support\ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PasswordResetRepositoryInterface::class, PasswordResetRepository::class);
        
        $this->app->bind(PasswordResetServiceInterface::class, function ($app) {
            return new PasswordResetService(
                $app->make(PasswordResetRepositoryInterface::class),
                $app->make(\App\Contracts\Services\ActivityLoggerInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}