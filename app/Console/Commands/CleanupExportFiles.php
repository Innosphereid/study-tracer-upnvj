<?php

namespace App\Console\Commands;

use App\Contracts\Services\ResultExportServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Console command to clean up old export files
 */
class CleanupExportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-export-files {--hours=24 : Delete files older than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old questionnaire export files';

    /**
     * @var ResultExportServiceInterface
     */
    protected $exportService;

    /**
     * Create a new command instance.
     *
     * @param ResultExportServiceInterface $exportService
     */
    public function __construct(ResultExportServiceInterface $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        
        if ($hours < 1) {
            $this->error('Hours must be at least 1');
            return Command::FAILURE;
        }
        
        $this->info("Cleaning up export files older than {$hours} hours...");
        
        try {
            $this->exportService->cleanupOldExports($hours);
            $this->info('Cleanup completed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup export files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error("Failed to cleanup export files: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
} 