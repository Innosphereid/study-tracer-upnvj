<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the password reset service provider
        $this->app->register(PasswordResetServiceProvider::class);
        
        // Register the rate limiting service provider
        $this->app->register(RateLimitingServiceProvider::class);

        // Register the questionnaire service provider
        $this->app->register(QuestionnaireServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}