<?php

namespace App\Services;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Contracts\Services\ResultExportServiceInterface;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for exporting questionnaire results in various formats
 */
class ResultExportService implements ResultExportServiceInterface
{
    /**
     * @var QuestionnaireRepositoryInterface
     */
    protected $questionnaireRepository;
    
    /**
     * @var ResponseRepositoryInterface
     */
    protected $responseRepository;
    
    /**
     * @var AnswerDetailRepositoryInterface
     */
    protected $answerDetailRepository;
    
    /**
     * @var string The storage disk to use for exports
     */
    protected $storageDisk = 'questionnaire_exports';
    
    /**
     * ResultExportService constructor.
     *
     * @param QuestionnaireRepositoryInterface $questionnaireRepository
     * @param ResponseRepositoryInterface $responseRepository
     * @param AnswerDetailRepositoryInterface $answerDetailRepository
     */
    public function __construct(
        QuestionnaireRepositoryInterface $questionnaireRepository,
        ResponseRepositoryInterface $responseRepository,
        AnswerDetailRepositoryInterface $answerDetailRepository
    ) {
        $this->questionnaireRepository = $questionnaireRepository;
        $this->responseRepository = $responseRepository;
        $this->answerDetailRepository = $answerDetailRepository;
    }
    
    /**
     * Export questionnaire results to CSV format
     *
     * @param int $questionnaireId ID of the questionnaire to export results for
     * @param string $format Export format (csv, excel, pdf)
     * @param array $options Additional export options
     * @return string Path to the exported file
     * 
     * @throws \InvalidArgumentException If the questionnaire does not exist
     * @throws \RuntimeException If the export fails
     */
    public function exportResults(int $questionnaireId, string $format = 'csv', array $options = []): string
    {
        $format = strtolower($format);
        
        // Validate and retrieve the questionnaire
        $questionnaire = $this->getQuestionnaire($questionnaireId);
        
        // Create export directory if it doesn't exist
        $this->ensureExportDirectoryExists($questionnaireId);
        
        // Generate the export based on format
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($questionnaire, $options);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }
    
    /**
     * Get the URL for downloading a previously exported file
     *
     * @param string $path Path to the exported file
     * @return string Download URL
     * 
     * @throws \InvalidArgumentException If the file does not exist
     */
    public function getDownloadUrl(string $path): string
    {
        if (!Storage::disk($this->storageDisk)->exists($path)) {
            throw new \InvalidArgumentException("Export file does not exist: {$path}");
        }
        
        // Generate a temporary signed URL or return a path that can be used by a controller
        return $path; // The path is returned and the controller will handle the download
    }
    
    /**
     * Clean up old export files
     *
     * @param int $olderThanHours Delete files older than this many hours
     * @return void
     */
    public function cleanupOldExports(int $olderThanHours = 24): void
    {
        Log::info('Cleaning up old export files', ['olderThanHours' => $olderThanHours]);
        
        $cutoffTime = Carbon::now()->subHours($olderThanHours);
        $files = Storage::disk($this->storageDisk)->allFiles();
        
        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(
                Storage::disk($this->storageDisk)->lastModified($file)
            );
            
            if ($lastModified->lt($cutoffTime)) {
                Log::info('Deleting old export file', ['file' => $file, 'lastModified' => $lastModified]);
                Storage::disk($this->storageDisk)->delete($file);
            }
        }
    }
    
    /**
     * Export questionnaire results to CSV format
     *
     * @param Questionnaire $questionnaire
     * @param array $options
     * @return string Path to the exported file
     * 
     * @throws \RuntimeException If the export fails
     */
    protected function exportToCsv(Questionnaire $questionnaire, array $options = []): string
    {
        Log::info('Exporting questionnaire results to CSV', [
            'questionnaireId' => $questionnaire->id,
            'options' => $options
        ]);
        
        try {
            // Generate CSV content
            $csvContent = $this->generateCsvContent($questionnaire, $options);
            
            // Generate filename
            $filename = $this->generateFilename($questionnaire, 'csv', $options);
            $relativePath = $questionnaire->id . '/' . $filename;
            
            // Save to storage
            Storage::disk($this->storageDisk)->put($relativePath, $csvContent);
            
            Log::info('Successfully exported questionnaire results to CSV', [
                'questionnaireId' => $questionnaire->id,
                'filename' => $filename,
                'size' => strlen($csvContent)
            ]);
            
            return $relativePath;
        } catch (\Exception $e) {
            Log::error('Failed to export questionnaire results to CSV', [
                'questionnaireId' => $questionnaire->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Failed to export questionnaire results: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Generate CSV content for a questionnaire
     *
     * @param Questionnaire $questionnaire
     * @param array $options
     * @return string CSV content
     */
    protected function generateCsvContent(Questionnaire $questionnaire, array $options = []): string
    {
        // Get responses without loading the 'answers' relationship
        $responses = $this->responseRepository->getByQuestionnaireId($questionnaire->id);
        
        if ($responses->isEmpty()) {
            return "No responses found\n";
        }
        
        // Prepare headers
        $headers = ['Response ID', 'Respondent', 'Created At', 'Completed At', 'IP Address'];
        
        // Add question headers
        $questions = [];
        foreach ($questionnaire->sections as $section) {
            foreach ($section->questions as $question) {
                $headers[] = $question->title;
                $questions[$question->id] = $question;
            }
        }
        
        // Start CSV output
        $csv = implode(',', array_map(function ($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\n";
        
        // Add response rows
        foreach ($responses as $response) {
            $row = [
                $response->id,
                $response->respondent_name ?? $response->respondent_email ?? 'Anonymous',
                $response->created_at->format('Y-m-d H:i:s'),
                $response->completed_at ? $response->completed_at->format('Y-m-d H:i:s') : 'Not Completed',
                $response->ip_address ?? 'N/A'
            ];
            
            // Check if there are answer details for this response
            $answerDetails = $this->answerDetailRepository->getByResponseId($response->id);
            $hasStructuredAnswers = $answerDetails->isNotEmpty();
            
            if ($hasStructuredAnswers) {
                // Create a map for easy lookup by question ID
                $answerMap = [];
                foreach ($answerDetails as $detail) {
                    $answerMap[$detail->question_id] = $detail;
                }
                
                // Add answers for each question
                foreach ($questions as $questionId => $question) {
                    $answerDetail = $answerMap[$questionId] ?? null;
                    $row[] = $this->formatAnswerValue($answerDetail, $question);
                }
            } else {
                // Use response_data as fallback
                $responseData = $response->response_data ?? [];
                
                // Add answers for each question
                foreach ($questions as $questionId => $question) {
                    $row[] = $responseData[$questionId] ?? '';
                }
            }
            
            // Add row to CSV
            $csv .= implode(',', array_map(function ($cell) {
                return '"' . str_replace('"', '""', $cell) . '"';
            }, $row)) . "\n";
        }
        
        return $csv;
    }
    
    /**
     * Format answer value based on question type
     *
     * @param mixed $answerDetail
     * @param mixed $question
     * @return string
     */
    protected function formatAnswerValue($answerDetail, $question): string
    {
        if (!$answerDetail) {
            return '';
        }
        
        $value = $answerDetail->answer_value;
        
        // Handle different question types
        switch ($question->question_type) {
            case 'checkbox':
                if (is_array($value)) {
                    return implode(', ', $value);
                }
                return $value;
            
            case 'file':
                if (is_array($value)) {
                    return implode(', ', array_map(function ($file) {
                        return $file['name'] ?? $file['filename'] ?? 'Unknown file';
                    }, $value));
                }
                return $value;
                
            default:
                if (is_array($value)) {
                    return json_encode($value);
                }
                return (string) $value;
        }
    }
    
    /**
     * Generate a unique filename for the export
     *
     * @param Questionnaire $questionnaire
     * @param string $format
     * @param array $options
     * @return string
     */
    protected function generateFilename(Questionnaire $questionnaire, string $format, array $options = []): string
    {
        $timestamp = Carbon::now()->format('Ymd_His');
        $title = Str::slug($questionnaire->title);
        $uniqueId = substr(md5(uniqid()), 0, 8);
        
        return "{$title}_export_{$timestamp}_{$uniqueId}.{$format}";
    }
    
    /**
     * Get a questionnaire by ID with its sections and questions
     *
     * @param int $questionnaireId
     * @return Questionnaire
     * 
     * @throws \InvalidArgumentException If the questionnaire does not exist
     */
    protected function getQuestionnaire(int $questionnaireId): Questionnaire
    {
        $questionnaire = $this->questionnaireRepository->find($questionnaireId, ['*'], ['sections.questions']);
        
        if (!$questionnaire) {
            Log::warning('Questionnaire not found for export', ['questionnaireId' => $questionnaireId]);
            throw new \InvalidArgumentException("Questionnaire not found: {$questionnaireId}");
        }
        
        return $questionnaire;
    }
    
    /**
     * Ensure the export directory exists for the questionnaire
     *
     * @param int $questionnaireId
     * @return void
     */
    protected function ensureExportDirectoryExists(int $questionnaireId): void
    {
        $directory = "{$questionnaireId}";
        
        if (!Storage::disk($this->storageDisk)->exists($directory)) {
            Storage::disk($this->storageDisk)->makeDirectory($directory);
        }
    }
} 