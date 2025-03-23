<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitingServiceProvider extends ServiceProvider
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
        // Configure rate limiting for password reset
        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email', '');
            
            return Limit::perMinute(5)->by($email.$request->ip());
        });
        
        // Configure rate limiting for OTP verification
        RateLimiter::for('verify-otp', function (Request $request) {
            $email = (string) $request->input('email', '');
            
            return Limit::perMinute(5)->by($email.$request->ip());
        });
    }
}