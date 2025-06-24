<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Provider untuk menginisialisasi layanan Excel
 */
class ExcelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register bindings if needed in the future
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Create temp directory for Excel exports
        $tempDir = storage_path('app/excel-temp');
        
        // Create directory if it doesn't exist
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }
}
