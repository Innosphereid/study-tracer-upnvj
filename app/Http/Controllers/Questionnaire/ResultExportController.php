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
        // Ensure questionnaireId is an integer
        $questionnaireId = (int) $questionnaireId;
        
        Log::info('Exporting results', [
            'questionnaireId' => $questionnaireId,
            'format' => $format
        ]);
        
        $questionnaire = $this->questionnaireService->getQuestionnaireById($questionnaireId);
        
        abort_if(!$questionnaire, 404, 'Kuesioner tidak ditemukan');
        
        try {
            // Generate export
            $exportPath = $this->exportService->exportResults($questionnaireId, $format);
            
            // Determine filename
            $filename = Str::slug($questionnaire->title) . '-results-' . now()->format('Y-m-d') . '.' . $format;
            
            // Stream the file to the browser
            $disk = 'questionnaire_exports';
            
            $fullPath = Storage::disk($disk)->path($exportPath);
            
            // Return file as a download
            return response()->download($fullPath, $filename, [
                'Content-Type' => $this->getContentType($format),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export results', [
                'questionnaireId' => $questionnaireId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to generate export: ' . $e->getMessage());
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
} 