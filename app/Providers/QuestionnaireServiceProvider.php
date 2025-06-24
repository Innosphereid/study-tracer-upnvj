<?php

namespace App\Providers;

use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Repositories\QuestionRepositoryInterface;
use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Contracts\Services\ResponseServiceInterface;
use App\Contracts\Services\ResultExportServiceInterface;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\SectionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\ResponseRepository;
use App\Repositories\AnswerDetailRepository;
use App\Services\QuestionnaireService;
use App\Services\ResponseService;
use App\Services\ResultExportService;
use Illuminate\Support\ServiceProvider;

class QuestionnaireServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(QuestionnaireRepositoryInterface::class, QuestionnaireRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(ResponseRepositoryInterface::class, ResponseRepository::class);
        $this->app->bind(AnswerDetailRepositoryInterface::class, AnswerDetailRepository::class);
        
        // Register Services
        $this->app->bind(QuestionnaireServiceInterface::class, QuestionnaireService::class);
        $this->app->bind(ResponseServiceInterface::class, ResponseService::class);
        $this->app->bind(ResultExportServiceInterface::class, ResultExportService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 