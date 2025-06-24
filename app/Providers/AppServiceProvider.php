<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        
        // Register repositories
        $this->app->bind(
            \App\Contracts\Repositories\QuestionnaireRepositoryInterface::class,
            \App\Repositories\QuestionnaireRepository::class
        );
        
        $this->app->bind(
            \App\Contracts\Repositories\SectionRepositoryInterface::class,
            \App\Repositories\SectionRepository::class
        );
        
        $this->app->bind(
            \App\Contracts\Repositories\QuestionRepositoryInterface::class,
            \App\Repositories\QuestionRepository::class
        );
        
        $this->app->bind(
            \App\Contracts\Repositories\ResponseRepositoryInterface::class,
            \App\Repositories\ResponseRepository::class
        );
        
        $this->app->bind(
            \App\Contracts\Repositories\AnswerDetailRepositoryInterface::class,
            \App\Repositories\AnswerDetailRepository::class
        );
        
        $this->app->bind(
            \App\Contracts\Services\AnswerDetailServiceInterface::class,
            \App\Services\AnswerDetailService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Set default string length for schema
        Schema::defaultStringLength(191);
    }
}