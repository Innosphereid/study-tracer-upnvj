<?php

namespace App\Http\Controllers\Questionnaire;

use App\Contracts\Services\QuestionnaireServiceInterface;
use App\Contracts\Services\ResultExportServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for handling questionnaire result exports
 */
class ResultExportController extends Controller
{
    /**
     * @var QuestionnaireServiceInterface
     */
    protected $questionnaireService;
    
    /**
     * @var ResultExportServiceInterface
     */
    protected $exportService;
    
    /**
     * ResultExportController constructor.
     *
     * @param QuestionnaireServiceInterface $questionnaireService
     * @param ResultExportServiceInterface $exportService
     */
    public function __construct(
        QuestionnaireServiceInterface $questionnaireService,
        ResultExportServiceInterface $exportService
    ) {
        $this->questionnaireService = $questionnaireService;
        $this->exportService = $exportService;
    }
    
    /**
     * Export questionnaire results in the specified format
     *
     * @param Request $request
     * @param string|int $questionnaireId
     * @param string $format
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request, $questionnaireId, string $format = 'csv'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            // Generate the export
            $path = $this->exportService->exportResults($questionnaireId, $format);
            
            // Get the file path
            $filePath = Storage::disk('questionnaire_exports')->path($path);
            
            // Check if the file exists
            if (!file_exists($filePath)) {
                throw new \RuntimeException("Export file not found: {$filePath}");
            }
            
            // Add a note in session if PDF was rendered with DomPDF fallback
            if ($format === 'pdf' && !$this->isWkhtmltopdfAvailable()) {
                session()->flash('info', 'PDF was generated using DomPDF renderer as wkhtmltopdf is not available on this server.');
            }
            
            // Generate download filename
            $filename = basename($filePath);
            
            // Return the file
            return response()->download($filePath, $filename, [
                'Content-Type' => $this->getContentType($format),
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Export failed', [
                'questionnaireId' => $questionnaireId,
                'format' => $format,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Redirect back with an error message
            return abort(500, "Failed to export results: {$e->getMessage()}");
        }
    }
    
    /**
     * Get the content type for the specified format
     *
     * @param string $format
     * @return string
     */
    protected function getContentType(string $format): string
    {
        switch (strtolower($format)) {
            case 'csv':
                return 'text/csv';
            case 'excel':
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'pdf':
                return 'application/pdf';
            default:
                return 'application/octet-stream';
        }
    }
    
    /**
     * Check if wkhtmltopdf is available and working
     *
     * @return bool
     */
    protected function isWkhtmltopdfAvailable(): bool
    {
        try {
            if (config('snappy.pdf.binary')) {
                $binary = config('snappy.pdf.binary');
                
                // Remove quotes if present
                $binary = trim($binary, '"\'');
                
                // Check if the binary exists
                if (!file_exists($binary)) {
                    return false;
                }
                
                // Try executing the binary with --version to check if it's working
                $output = [];
                $exitCode = 0;
                exec("\"$binary\" --version 2>&1", $output, $exitCode);
                
                return $exitCode === 0;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking wkhtmltopdf availability', [
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }
} 