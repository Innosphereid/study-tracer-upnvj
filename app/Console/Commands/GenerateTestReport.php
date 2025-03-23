<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GenerateTestReport extends Command
{
    protected $signature = 'test:report 
                            {--filter= : The PHPUnit filter to apply}
                            {--coverage-html= : Generate HTML coverage report}';
    
    protected $description = 'Run tests and generate a timestamped report file with coverage';

    public function handle()
    {
        $timestamp = Carbon::now()->format('Y_m_d_His');
        $reportPath = storage_path("test-reports/test_report_{$timestamp}.txt");
        
        $this->info("Generating test report at: {$reportPath}");
        
        // Create directory if it doesn't exist
        if (!File::exists(dirname($reportPath))) {
            File::makeDirectory(dirname($reportPath), 0755, true);
        }
        
        // Build the command
        $command = ['php', 'artisan', 'test', '--no-ansi'];
        
        
        // Add HTML coverage report if requested
        if ($coverageHtml = $this->option('coverage-html')) {
            $coveragePath = $coverageHtml ?: storage_path("test-reports/coverage_{$timestamp}");
            $command[] = '--coverage-html=' . $coveragePath;
        }
        
        // Add filter if provided
        if ($filter = $this->option('filter')) {
            $command[] = '--filter=' . $filter;
        }
        
        // Execute the test command
        $process = new Process($command);
        $process->setTimeout(null); // No timeout for long coverage analysis
        $process->run();
        
        // Get the output
        $output = $process->getOutput();
        
        // Add header information
        $report = "Test Report with Coverage - Generated at: " . Carbon::now()->toDateTimeString() . PHP_EOL;
        $report .= "==========================================================" . PHP_EOL;
        $report .= PHP_EOL . $output;
        
        // Write to file
        File::put($reportPath, $report);
        
        // Output to console
        $this->line($output);
        $this->info("Test report saved to: {$reportPath}");
        
        if ($coverageHtml) {
            $this->info("HTML coverage report saved to: {$coveragePath}");
        }
        
        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}