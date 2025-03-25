<?php

namespace App\Providers;

use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Repositories\QuestionRepository;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\ResponseRepository;
use App\Repositories\SectionRepository;
use App\Services\QuestionnaireService;
use Illuminate\Support\ServiceProvider;

class QuestionnaireServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(QuestionnaireRepositoryInterface::class, QuestionnaireRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(ResponseRepositoryInterface::class, ResponseRepository::class);
        
        // Register services
        $this->app->bind(QuestionnaireServiceInterface::class, QuestionnaireService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}