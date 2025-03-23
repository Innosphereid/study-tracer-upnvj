<?php

use App\Http\Middleware\CheckUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the CheckUserRole middleware with the 'role' alias
        $middleware->alias([
            'role' => CheckUserRole::class,
        ]);
        
        // Configure rate limiting
        RateLimiter::for('password-reset', function ($request) {
            $email = (string) $request->email;
            
            return Limit::perMinute(5)->by($email.$request->ip());
        });
        
        RateLimiter::for('verify-otp', function ($request) {
            $email = (string) $request->email;
            
            return Limit::perMinute(5)->by($email.$request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();