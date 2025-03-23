<?php

namespace App\Providers;

use App\Contracts\Services\AuthenticatorInterface;
use App\Contracts\Services\AuthenticationServiceInterface;
use App\Services\AuthenticatorService;
use App\Services\AuthenticationService;
use Illuminate\Support\ServiceProvider;

class AuthServicesProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthenticatorInterface::class, AuthenticatorService::class);
        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}