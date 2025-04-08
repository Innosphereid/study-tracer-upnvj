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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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
            case 'pdf':
                return $this->exportToPdf($questionnaire, $options);
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
    
    /**
     * Export questionnaire results to PDF format
     *
     * @param Questionnaire $questionnaire
     * @param array $options
     * @return string Path to the exported file
     * 
     * @throws \RuntimeException If the export fails
     */
    protected function exportToPdf(Questionnaire $questionnaire, array $options = []): string
    {
        Log::info('Exporting questionnaire results to PDF', [
            'questionnaireId' => $questionnaire->id,
            'options' => $options
        ]);
        
        try {
            // Get responses without loading the 'answers' relationship
            $responses = $this->responseRepository->getByQuestionnaireId($questionnaire->id);
            
            if ($responses->isEmpty()) {
                throw new \RuntimeException("No responses found for questionnaire {$questionnaire->id}");
            }
            
            // Prepare data for PDF generation
            $statistics = $this->calculateStatistics($questionnaire, $responses);
            $questionData = $this->prepareQuestionData($questionnaire, $responses);
            $charts = $this->generateCharts($questionData, $questionnaire);
            
            // Generate filename
            $filename = $this->generateFilename($questionnaire, 'pdf', $options);
            $relativePath = $questionnaire->id . '/' . $filename;
            $absolutePath = Storage::disk($this->storageDisk)->path($relativePath);
            
            // Ensure the directory exists
            $directory = dirname($absolutePath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Generate the HTML content
            $html = View::make('exports.pdf.results', [
                'questionnaire' => $questionnaire,
                'statistics' => $statistics,
                'questionData' => $questionData,
                'charts' => $charts,
                'user' => Auth::user(),
                'fallbackVisualizerMap' => $this->getFallbackVisualizerMap(),
            ])->render();
            
            // Check for available PDF libraries and choose the best one
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                // Prefer DomPDF as it works without external dependencies
                Log::info('Using DomPDF for PDF generation');
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4');
                $pdf->save($absolutePath);
            } 
            else if (class_exists('\Barryvdh\Snappy\Facades\SnappyPdf') && $this->isWkhtmltopdfAvailable()) {
                // Use Snappy (wkhtmltopdf) only if the binary is available and accessible
                Log::info('Using wkhtmltopdf for PDF generation');
                $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html);
                $pdf->setOption('enable-local-file-access', true);
                $pdf->setOption('encoding', 'UTF-8');
                $pdf->setOption('margin-top', '10mm');
                $pdf->setOption('margin-right', '10mm');
                $pdf->setOption('margin-bottom', '10mm');
                $pdf->setOption('margin-left', '10mm');
                $pdf->setOption('page-size', 'A4');
                
                // Save the PDF
                $pdf->save($absolutePath);
            } 
            else {
                // Fallback if no PDF library is available
                Log::warning('No functional PDF library found. Saving as HTML instead.', [
                    'questionnaireId' => $questionnaire->id
                ]);
                
                // Save to storage as HTML for now (will not be a valid PDF)
                Storage::disk($this->storageDisk)->put($relativePath, $html);
            }
            
            Log::info('Successfully exported questionnaire results to PDF', [
                'questionnaireId' => $questionnaire->id,
                'filename' => $filename,
            ]);
            
            return $relativePath;
        } catch (\Exception $e) {
            Log::error('Failed to export questionnaire results to PDF', [
                'questionnaireId' => $questionnaire->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Failed to export questionnaire results to PDF: {$e->getMessage()}", 0, $e);
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
                    Log::warning('wkhtmltopdf binary not found at configured path', ['path' => $binary]);
                    return false;
                }
                
                // Try executing the binary with --version to check if it's working
                $output = [];
                $exitCode = 0;
                exec("\"$binary\" --version 2>&1", $output, $exitCode);
                
                if ($exitCode !== 0) {
                    Log::warning('wkhtmltopdf binary exists but failed to execute', [
                        'path' => $binary,
                        'exit_code' => $exitCode,
                        'output' => implode("\n", $output)
                    ]);
                    return false;
                }
                
                Log::info('wkhtmltopdf is available and working', [
                    'path' => $binary,
                    'version' => $output[0] ?? 'Unknown'
                ]);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking wkhtmltopdf availability', [
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }
    
    /**
     * Calculate statistics for the questionnaire
     *
     * @param Questionnaire $questionnaire
     * @param \Illuminate\Support\Collection $responses
     * @return array
     */
    protected function calculateStatistics(Questionnaire $questionnaire, $responses): array
    {
        $totalResponses = $responses->count();
        $completedResponses = $responses->whereNotNull('completed_at')->count();
        $sectionsCount = $questionnaire->sections->count();
        $questionsCount = $questionnaire->sections->sum(function ($section) {
            return $section->questions->count();
        });
        
        $completionRate = $totalResponses > 0 ? ($completedResponses / $totalResponses) * 100 : 0;
        
        // Calculate response by date
        $responseByDate = [];
        foreach ($responses as $response) {
            $date = $response->created_at->format('Y-m-d');
            if (!isset($responseByDate[$date])) {
                $responseByDate[$date] = 0;
            }
            $responseByDate[$date]++;
        }
        
        return [
            'total_responses' => $totalResponses,
            'completed_responses' => $completedResponses,
            'completion_rate' => $completionRate,
            'sections_count' => $sectionsCount,
            'questions_count' => $questionsCount,
            'response_by_date' => $responseByDate,
        ];
    }
    
    /**
     * Prepare question data for the PDF
     *
     * @param Questionnaire $questionnaire
     * @param \Illuminate\Support\Collection $responses
     * @return array
     */
    protected function prepareQuestionData(Questionnaire $questionnaire, $responses): array
    {
        $questionData = [];
        
        foreach ($questionnaire->sections as $section) {
            foreach ($section->questions as $question) {
                // Get answers for this question
                $answerDetails = $this->answerDetailRepository->getByQuestionId($question->id);
                
                if ($answerDetails->isEmpty()) {
                    continue;
                }
                
                // Process answers based on question type
                $data = $this->processAnswers($question, $answerDetails);
                
                $questionData[$question->id] = $data;
            }
        }
        
        return $questionData;
    }
    
    /**
     * Process answers for a question
     *
     * @param \App\Models\Question $question
     * @param \Illuminate\Support\Collection $answerDetails
     * @return array
     */
    protected function processAnswers($question, $answerDetails): array
    {
        $data = [
            'responses' => [],
        ];
        
        // Common data for all question types
        foreach ($answerDetails as $answer) {
            $responseData = [
                'id' => $answer->id,
                'response_id' => $answer->response_id,
                'created_at' => $answer->created_at,
            ];
            
            // Process based on question type
            switch ($question->question_type) {
                case 'numeric':
                    $value = floatval($answer->answer_value);
                    $responseData['value'] = $value;
                    break;
                
                case 'text':
                case 'textarea':
                    $responseData['text'] = $answer->answer_value;
                    break;
                
                case 'multichoice':
                    $selectedOptions = is_array($answer->answer_value) ? $answer->answer_value : [$answer->answer_value];
                    $responseData['selected_options'] = $selectedOptions;
                    break;
                
                case 'yesno':
                    $responseData['value'] = filter_var($answer->answer_value, FILTER_VALIDATE_BOOLEAN);
                    break;
                
                case 'likert':
                    $value = intval($answer->answer_value);
                    $responseData['value'] = $value;
                    $responseData['option_label'] = $this->getLikertOptionLabel($question, $value);
                    break;
                
                case 'ranking':
                    if (is_array($answer->answer_value)) {
                        $responseData['ranking'] = $answer->answer_value;
                    } else if (is_string($answer->answer_value) && $this->isJson($answer->answer_value)) {
                        $responseData['ranking'] = json_decode($answer->answer_value, true);
                    } else {
                        $responseData['ranking'] = [];
                    }
                    break;
                
                case 'date':
                    $responseData['date'] = $answer->answer_value;
                    break;
                
                case 'file':
                    if (is_array($answer->answer_value)) {
                        $files = $answer->answer_value;
                    } elseif (is_string($answer->answer_value) && $this->isJson($answer->answer_value)) {
                        $files = json_decode($answer->answer_value, true);
                    } else {
                        $files = [];
                    }
                    
                    // Process file info
                    $processedFiles = [];
                    foreach ($files as $file) {
                        $fileSize = isset($file['size']) ? (int)$file['size'] : 0;
                        $processedFiles[] = [
                            'name' => $file['name'] ?? $file['filename'] ?? 'Unknown',
                            'type' => $this->getFileType($file['name'] ?? $file['filename'] ?? ''),
                            'size' => $fileSize,
                            'size_formatted' => $this->formatFileSize($fileSize),
                            'url' => $file['url'] ?? '',
                        ];
                    }
                    
                    $responseData['files'] = $processedFiles;
                    break;
                
                default:
                    $responseData['value'] = $answer->answer_value;
            }
            
            $data['responses'][] = $responseData;
        }
        
        // Calculate statistics based on question type
        switch ($question->question_type) {
            case 'numeric':
                $data['stats'] = $this->calculateNumericStats($data['responses']);
                $data['distribution'] = $this->calculateNumericDistribution($data['responses']);
                break;
            
            case 'text':
            case 'textarea':
                $data['stats'] = $this->calculateTextStats($data['responses']);
                $data['common_words'] = $this->calculateCommonWords($data['responses']);
                break;
            
            case 'multichoice':
                $data['options_summary'] = $this->calculateMultiChoiceStats($question, $data['responses']);
                break;
            
            case 'yesno':
                $yesCount = count(array_filter($data['responses'], function ($response) {
                    return $response['value'] === true;
                }));
                $noCount = count($data['responses']) - $yesCount;
                
                $data['yes_count'] = $yesCount;
                $data['no_count'] = $noCount;
                $data['yes_percentage'] = count($data['responses']) > 0 ? ($yesCount / count($data['responses'])) * 100 : 0;
                $data['no_percentage'] = count($data['responses']) > 0 ? ($noCount / count($data['responses'])) * 100 : 0;
                break;
            
            case 'likert':
                $data['stats'] = $this->calculateLikertStats($question, $data['responses']);
                $data['scale_summary'] = $this->calculateLikertDistribution($question, $data['responses']);
                break;
            
            case 'ranking':
                $data['average_rankings'] = $this->calculateRankingStats($question, $data['responses']);
                $data['top_ranked'] = $this->calculateTopRankedItems($question, $data['responses']);
                break;
            
            case 'date':
                $data['stats'] = $this->calculateDateStats($data['responses']);
                $data['date_distribution'] = $this->calculateDateDistribution($data['responses']);
                break;
            
            case 'file':
                $data['stats'] = $this->calculateFileStats($data['responses']);
                $data['file_types'] = $this->calculateFileTypeDistribution($data['responses']);
                $data['file_size_distribution'] = $this->calculateFileSizeDistribution($data['responses']);
                break;
        }
        
        return $data;
    }
    
    /**
     * Generate charts for the PDF
     *
     * @param array $questionData
     * @param Questionnaire $questionnaire
     * @return array
     */
    protected function generateCharts(array $questionData, Questionnaire $questionnaire): array
    {
        // When using DomPDF, we rely on the HTML/CSS visualizations in the blade templates
        // If we were using wkhtmltopdf, we could generate chart images here
        // For now, return an empty array as the visualization is handled by CSS in the templates
        
        $charts = [];
        
        // Add placeholder for charts that might be implemented in the future
        // In a real implementation with a chart library, you'd generate chart images here
        foreach ($questionData as $questionId => $data) {
            $charts[$questionId] = '';
        }
        
        // Placeholder for response timeline
        $charts['response_timeline'] = '';
        
        return $charts;
    }
    
    /**
     * Get the label for a Likert scale option
     *
     * @param \App\Models\Question $question
     * @param int $value
     * @return string
     */
    protected function getLikertOptionLabel($question, $value): string
    {
        $options = $question->properties['scale_options'] ?? [];
        
        foreach ($options as $option) {
            if (isset($option['value']) && (int)$option['value'] === $value) {
                return $option['label'] ?? "Option {$value}";
            }
        }
        
        return "Option {$value}";
    }
    
    /**
     * Calculate numeric statistics
     *
     * @param array $responses
     * @return array
     */
    protected function calculateNumericStats(array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $values = array_map(function ($response) {
            return $response['value'] ?? 0;
        }, $responses);
        
        $values = array_filter($values, function($value) {
            return is_numeric($value);
        });
        
        if (empty($values)) {
            return [];
        }
        
        sort($values);
        $count = count($values);
        $sum = array_sum($values);
        $average = $sum / $count;
        
        // Calculate median
        $middle = floor($count / 2);
        $median = $count % 2 === 0 
            ? ($values[$middle - 1] + $values[$middle]) / 2 
            : $values[$middle];
        
        // Calculate standard deviation
        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $average, 2);
        }
        $stdDev = sqrt($variance / $count);
        
        return [
            'count' => $count,
            'sum' => $sum,
            'min' => min($values),
            'max' => max($values),
            'average' => $average,
            'median' => $median,
            'std_dev' => $stdDev,
        ];
    }
    
    /**
     * Calculate distribution of numeric values
     *
     * @param array $responses
     * @param int $bins Number of bins/ranges for the distribution
     * @return array
     */
    protected function calculateNumericDistribution(array $responses, int $bins = 5): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $values = array_map(function ($response) {
            return $response['value'] ?? 0;
        }, $responses);
        
        $values = array_filter($values, function($value) {
            return is_numeric($value);
        });
        
        if (empty($values)) {
            return [];
        }
        
        $min = min($values);
        $max = max($values);
        
        // If all values are the same, create a single bin
        if ($min === $max) {
            return [
                [
                    'label' => "All values are {$min}",
                    'min' => $min,
                    'max' => $max,
                    'count' => count($values),
                    'percentage' => 100,
                ]
            ];
        }
        
        // Calculate bin size
        $step = ($max - $min) / $bins;
        if ($step === 0) {
            $step = 1; // Fallback if max-min is too small
        }
        
        // Initialize bins
        $distribution = [];
        for ($i = 0; $i < $bins; $i++) {
            $binMin = $min + ($i * $step);
            $binMax = $binMin + $step;
            
            // Make sure the last bin includes the maximum value
            if ($i === $bins - 1) {
                $binMax = $max;
            }
            
            $distribution[] = [
                'label' => $binMin === $binMax 
                    ? number_format($binMin, 2) 
                    : number_format($binMin, 2) . ' - ' . number_format($binMax, 2),
                'min' => $binMin,
                'max' => $binMax,
                'count' => 0,
                'percentage' => 0,
            ];
        }
        
        // Count values in each bin
        foreach ($values as $value) {
            for ($i = 0; $i < $bins; $i++) {
                $binMin = $distribution[$i]['min'];
                $binMax = $distribution[$i]['max'];
                
                if ($value >= $binMin && ($value < $binMax || ($i === $bins - 1 && $value <= $binMax))) {
                    $distribution[$i]['count']++;
                    break;
                }
            }
        }
        
        // Calculate percentages
        $total = count($values);
        foreach ($distribution as &$bin) {
            $bin['percentage'] = $total > 0 ? ($bin['count'] / $total) * 100 : 0;
        }
        
        return $distribution;
    }
    
    /**
     * Calculate multi-choice option statistics
     *
     * @param \App\Models\Question $question
     * @param array $responses
     * @return array
     */
    protected function calculateMultiChoiceStats($question, array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $options = $question->properties['options'] ?? [];
        $optionMap = [];
        
        // Initialize option map
        foreach ($options as $option) {
            $label = $option['label'] ?? '';
            $value = $option['value'] ?? '';
            
            if (!empty($value)) {
                $optionMap[$value] = [
                    'label' => $label,
                    'count' => 0,
                    'percentage' => 0,
                ];
            }
        }
        
        // If "other" is enabled, add an entry for it
        if (isset($question->properties['allow_other']) && $question->properties['allow_other']) {
            $optionMap['other'] = [
                'label' => 'Other',
                'count' => 0,
                'percentage' => 0,
            ];
        }
        
        // Count selected options
        $totalSelections = 0;
        foreach ($responses as $response) {
            $selectedOptions = $response['selected_options'] ?? [];
            
            foreach ($selectedOptions as $selected) {
                if (isset($optionMap[$selected])) {
                    $optionMap[$selected]['count']++;
                    $totalSelections++;
                } elseif (isset($optionMap['other'])) {
                    // Handle custom "other" responses
                    $optionMap['other']['count']++;
                    $totalSelections++;
                }
            }
        }
        
        // Calculate percentages
        $totalResponses = count($responses);
        foreach ($optionMap as &$option) {
            $option['percentage'] = $totalResponses > 0 ? ($option['count'] / $totalResponses) * 100 : 0;
        }
        
        // Format for output
        $result = [];
        foreach ($optionMap as $value => $option) {
            $result[] = [
                'value' => $value,
                'label' => $option['label'],
                'count' => $option['count'],
                'percentage' => $option['percentage'],
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate Likert scale statistics
     *
     * @param \App\Models\Question $question
     * @param array $responses
     * @return array
     */
    protected function calculateLikertStats($question, array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $values = array_map(function ($response) {
            return $response['value'] ?? 0;
        }, $responses);
        
        $values = array_filter($values, function($value) {
            return is_numeric($value);
        });
        
        if (empty($values)) {
            return [];
        }
        
        // Calculate average
        $average = array_sum($values) / count($values);
        
        // Calculate median
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);
        $median = $count % 2 === 0 
            ? ($values[$middle - 1] + $values[$middle]) / 2 
            : $values[$middle];
        
        // Calculate mode
        $valueFrequency = array_count_values($values);
        arsort($valueFrequency);
        $mode = key($valueFrequency);
        
        return [
            'average' => $average,
            'median' => $median,
            'mode' => $mode,
        ];
    }
    
    /**
     * Calculate Likert scale distribution
     *
     * @param \App\Models\Question $question
     * @param array $responses
     * @return array
     */
    protected function calculateLikertDistribution($question, array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $scaleOptions = $question->properties['scale_options'] ?? [];
        $distribution = [];
        
        // Initialize with all possible scale options
        foreach ($scaleOptions as $option) {
            $value = $option['value'] ?? '';
            $label = $option['label'] ?? '';
            
            if (!empty($value)) {
                $distribution[$value] = [
                    'value' => $value,
                    'label' => $label,
                    'count' => 0,
                    'percentage' => 0,
                ];
            }
        }
        
        // Count responses for each option
        foreach ($responses as $response) {
            $value = $response['value'] ?? null;
            
            if ($value !== null && isset($distribution[$value])) {
                $distribution[$value]['count']++;
            }
        }
        
        // Calculate percentages
        $totalResponses = count($responses);
        foreach ($distribution as &$option) {
            $option['percentage'] = $totalResponses > 0 ? ($option['count'] / $totalResponses) * 100 : 0;
        }
        
        return array_values($distribution);
    }
    
    /**
     * Calculate rankings statistics
     *
     * @param \App\Models\Question $question
     * @param array $responses
     * @return array
     */
    protected function calculateRankingStats($question, array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $rankingOptions = $question->properties['ranking_options'] ?? [];
        $itemStats = [];
        
        // Initialize stats for each item
        foreach ($rankingOptions as $option) {
            $value = $option['value'] ?? '';
            $label = $option['label'] ?? '';
            
            if (!empty($value)) {
                $itemStats[$value] = [
                    'value' => $value,
                    'label' => $label,
                    'total_rank' => 0,
                    'count' => 0,
                    'positions' => [],
                ];
            }
        }
        
        // Process each response
        foreach ($responses as $response) {
            if (!isset($response['ranking']) || !is_array($response['ranking'])) {
                continue;
            }
            
            $ranking = array_values($response['ranking']);
            
            foreach ($ranking as $position => $itemValue) {
                if (!isset($itemStats[$itemValue])) {
                    continue;
                }
                
                $itemStats[$itemValue]['total_rank'] += ($position + 1);
                $itemStats[$itemValue]['count']++;
                
                if (!isset($itemStats[$itemValue]['positions'][$position])) {
                    $itemStats[$itemValue]['positions'][$position] = 0;
                }
                $itemStats[$itemValue]['positions'][$position]++;
            }
        }
        
        // Calculate average rankings
        $result = [];
        foreach ($itemStats as $value => $stats) {
            if ($stats['count'] > 0) {
                $averageRank = $stats['total_rank'] / $stats['count'];
                
                $result[] = [
                    'value' => $value,
                    'label' => $stats['label'],
                    'average_rank' => $averageRank,
                    'position_counts' => $stats['positions'],
                ];
            }
        }
        
        // Sort by average rank (lowest/best first)
        usort($result, function($a, $b) {
            return $a['average_rank'] <=> $b['average_rank'];
        });
        
        return $result;
    }
    
    /**
     * Calculate top ranked items
     *
     * @param \App\Models\Question $question
     * @param array $responses
     * @return array
     */
    protected function calculateTopRankedItems($question, array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $rankingOptions = $question->properties['ranking_options'] ?? [];
        $topRanked = [];
        
        // Initialize counts for each item
        foreach ($rankingOptions as $option) {
            $value = $option['value'] ?? '';
            $label = $option['label'] ?? '';
            
            if (!empty($value)) {
                $topRanked[$value] = [
                    'value' => $value,
                    'label' => $label,
                    'count' => 0,
                ];
            }
        }
        
        // Count responses where each item was ranked #1
        foreach ($responses as $response) {
            if (!isset($response['ranking']) || !is_array($response['ranking']) || empty($response['ranking'])) {
                continue;
            }
            
            $ranking = array_values($response['ranking']);
            $topItem = $ranking[0] ?? null;
            
            if ($topItem && isset($topRanked[$topItem])) {
                $topRanked[$topItem]['count']++;
            }
        }
        
        // Calculate percentages
        $totalResponses = count(array_filter($responses, function($response) {
            return !empty($response['ranking']);
        }));
        
        foreach ($topRanked as &$item) {
            $item['percentage'] = $totalResponses > 0 ? ($item['count'] / $totalResponses) * 100 : 0;
        }
        
        // Convert to array and sort by count (highest first)
        $result = array_values($topRanked);
        usort($result, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return $result;
    }
    
    /**
     * Check if a string is valid JSON
     *
     * @param string $string
     * @return bool
     */
    protected function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Calculate date question statistics
     *
     * @param array $responses
     * @return array
     */
    protected function calculateDateStats(array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $dates = [];
        $years = [];
        $months = [];
        
        foreach ($responses as $response) {
            if (!isset($response['date']) || empty($response['date'])) {
                continue;
            }
            
            try {
                $date = \Carbon\Carbon::parse($response['date']);
                $dates[] = $date;
                $years[] = $date->year;
                $months[] = $date->format('Y-m');
            } catch (\Exception $e) {
                continue;
            }
        }
        
        if (empty($dates)) {
            return [];
        }
        
        // Sort dates
        usort($dates, function($a, $b) {
            return $a->timestamp <=> $b->timestamp;
        });
        
        // Calculate most common year and month
        $yearCounts = array_count_values($years);
        $monthCounts = array_count_values($months);
        arsort($yearCounts);
        arsort($monthCounts);
        
        return [
            'earliest_date' => $dates[0]->toDateString(),
            'latest_date' => $dates[count($dates) - 1]->toDateString(),
            'most_common_year' => key($yearCounts),
            'most_common_month' => count($monthCounts) > 0 ? 
                \Carbon\Carbon::parse(key($monthCounts))->format('F Y') : 
                null,
        ];
    }
    
    /**
     * Calculate date distribution by years or months
     *
     * @param array $responses
     * @param string $groupBy 'year' or 'month'
     * @return array
     */
    protected function calculateDateDistribution(array $responses, string $groupBy = 'year'): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $distribution = [];
        $totalDates = 0;
        
        foreach ($responses as $response) {
            if (!isset($response['date']) || empty($response['date'])) {
                continue;
            }
            
            try {
                $date = \Carbon\Carbon::parse($response['date']);
                $key = $groupBy === 'year' ? $date->year : $date->format('Y-m');
                $label = $groupBy === 'year' ? (string)$date->year : $date->format('F Y');
                
                if (!isset($distribution[$key])) {
                    $distribution[$key] = [
                        'key' => $key,
                        'label' => $label,
                        'count' => 0,
                        'percentage' => 0,
                    ];
                }
                
                $distribution[$key]['count']++;
                $totalDates++;
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Sort by key
        ksort($distribution);
        
        // Calculate percentages
        foreach ($distribution as &$period) {
            $period['percentage'] = $totalDates > 0 ? ($period['count'] / $totalDates) * 100 : 0;
        }
        
        return array_values($distribution);
    }
    
    /**
     * Calculate file upload statistics
     *
     * @param array $responses
     * @return array
     */
    protected function calculateFileStats(array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $totalFiles = 0;
        $totalSize = 0;
        $responsesWithFiles = 0;
        
        foreach ($responses as $response) {
            if (!isset($response['files']) || !is_array($response['files']) || empty($response['files'])) {
                continue;
            }
            
            $files = $response['files'];
            $fileCount = count($files);
            
            if ($fileCount > 0) {
                $totalFiles += $fileCount;
                $responsesWithFiles++;
                
                // Sum up file sizes
                foreach ($files as $file) {
                    $totalSize += isset($file['size']) ? (int)$file['size'] : 0;
                }
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'avg_files_per_response' => $responsesWithFiles > 0 ? $totalFiles / $responsesWithFiles : 0,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatFileSize($totalSize),
            'responses_with_files' => $responsesWithFiles,
        ];
    }
    
    /**
     * Calculate file type distribution
     *
     * @param array $responses
     * @return array
     */
    protected function calculateFileTypeDistribution(array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $fileTypes = [];
        $totalFiles = 0;
        
        foreach ($responses as $response) {
            if (!isset($response['files']) || !is_array($response['files'])) {
                continue;
            }
            
            foreach ($response['files'] as $file) {
                $type = $file['type'] ?? 'Unknown';
                
                if (!isset($fileTypes[$type])) {
                    $fileTypes[$type] = [
                        'type' => $type,
                        'label' => $this->getReadableFileType($type),
                        'count' => 0,
                        'percentage' => 0,
                    ];
                }
                
                $fileTypes[$type]['count']++;
                $totalFiles++;
            }
        }
        
        // Calculate percentages
        foreach ($fileTypes as &$type) {
            $type['percentage'] = $totalFiles > 0 ? ($type['count'] / $totalFiles) * 100 : 0;
        }
        
        // Sort by count (most common first)
        uasort($fileTypes, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return array_values($fileTypes);
    }
    
    /**
     * Calculate file size distribution
     *
     * @param array $responses
     * @param int $bins Number of bins for size ranges
     * @return array
     */
    protected function calculateFileSizeDistribution(array $responses, int $bins = 5): array
    {
        if (empty($responses)) {
            return [];
        }
        
        // Collect all file sizes
        $fileSizes = [];
        
        foreach ($responses as $response) {
            if (!isset($response['files']) || !is_array($response['files'])) {
                continue;
            }
            
            foreach ($response['files'] as $file) {
                if (isset($file['size']) && is_numeric($file['size'])) {
                    $fileSizes[] = (int)$file['size'];
                }
            }
        }
        
        if (empty($fileSizes)) {
            return [];
        }
        
        $min = min($fileSizes);
        $max = max($fileSizes);
        
        // If all files are the same size
        if ($min === $max) {
            return [
                [
                    'label' => $this->formatFileSize($min),
                    'min' => $min,
                    'max' => $max,
                    'count' => count($fileSizes),
                    'percentage' => 100,
                ]
            ];
        }
        
        // Calculate bin size
        $step = ($max - $min) / $bins;
        
        // Initialize bins
        $distribution = [];
        for ($i = 0; $i < $bins; $i++) {
            $binMin = $min + ($i * $step);
            $binMax = $binMin + $step;
            
            // Make sure the last bin includes the maximum value
            if ($i === $bins - 1) {
                $binMax = $max;
            }
            
            $distribution[] = [
                'label' => $this->formatFileSize($binMin) . ' - ' . $this->formatFileSize($binMax),
                'min' => $binMin,
                'max' => $binMax,
                'count' => 0,
                'percentage' => 0,
            ];
        }
        
        // Count files in each bin
        foreach ($fileSizes as $size) {
            for ($i = 0; $i < $bins; $i++) {
                $binMin = $distribution[$i]['min'];
                $binMax = $distribution[$i]['max'];
                
                if ($size >= $binMin && ($size < $binMax || ($i === $bins - 1 && $size <= $binMax))) {
                    $distribution[$i]['count']++;
                    break;
                }
            }
        }
        
        // Calculate percentages
        $total = count($fileSizes);
        foreach ($distribution as &$bin) {
            $bin['percentage'] = $total > 0 ? ($bin['count'] / $total) * 100 : 0;
        }
        
        return $distribution;
    }
    
    /**
     * Get file type from file name
     *
     * @param string $filename
     * @return string
     */
    protected function getFileType(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return $extension ? strtolower($extension) : 'Unknown';
    }
    
    /**
     * Get readable file type name
     *
     * @param string $type
     * @return string
     */
    protected function getReadableFileType(string $type): string
    {
        $type = strtolower($type);
        
        $typeMap = [
            'pdf' => 'PDF Document',
            'doc' => 'Word Document',
            'docx' => 'Word Document',
            'xls' => 'Excel Spreadsheet',
            'xlsx' => 'Excel Spreadsheet',
            'ppt' => 'PowerPoint',
            'pptx' => 'PowerPoint',
            'txt' => 'Text File',
            'csv' => 'CSV File',
            'jpg' => 'JPEG Image',
            'jpeg' => 'JPEG Image',
            'png' => 'PNG Image',
            'gif' => 'GIF Image',
            'zip' => 'ZIP Archive',
            'rar' => 'RAR Archive',
            'mp3' => 'MP3 Audio',
            'mp4' => 'MP4 Video',
            'avi' => 'AVI Video',
            'mov' => 'QuickTime Video',
        ];
        
        return $typeMap[$type] ?? ucfirst($type);
    }
    
    /**
     * Format file size to human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatFileSize(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get mapping of question types to fallback visualizer templates
     * 
     * @return array
     */
    protected function getFallbackVisualizerMap(): array
    {
        return [
            'textarea' => 'text',     // Use text template as fallback for textarea
            'longtext' => 'text',     // Use text template for longtext as well
            'text' => 'text',         // Default
            'multichoice' => 'multi-choice', // Map to correct filename
            'checkbox' => 'multi-choice',   // Use multi-choice for checkbox
            'select' => 'multi-choice',     // Use multi-choice for select
            'file' => 'file-upload',        // Map to correct filename
            'date' => 'date-response',      // Map to correct filename
        ];
    }
    
    /**
     * Calculate statistics for text responses
     *
     * @param array $responses
     * @return array
     */
    protected function calculateTextStats(array $responses): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $totalLength = 0;
        $nonEmptyCount = 0;
        $wordCounts = [];
        
        foreach ($responses as $response) {
            if (!isset($response['text']) || empty($response['text'])) {
                continue;
            }
            
            $text = trim($response['text']);
            if (!empty($text)) {
                $length = strlen($text);
                $totalLength += $length;
                $nonEmptyCount++;
                
                $words = str_word_count($text, 0);
                $wordCounts[] = $words;
            }
        }
        
        $stats = [
            'total_responses' => count($responses),
            'non_empty_responses' => $nonEmptyCount,
            'empty_responses' => count($responses) - $nonEmptyCount,
            'avg_length' => $nonEmptyCount > 0 ? round($totalLength / $nonEmptyCount) : 0,
        ];
        
        if (!empty($wordCounts)) {
            $stats['avg_words'] = round(array_sum($wordCounts) / count($wordCounts));
            $stats['max_words'] = max($wordCounts);
            $stats['min_words'] = min($wordCounts);
        }
        
        return $stats;
    }
    
    /**
     * Calculate common words/phrases in text responses
     *
     * @param array $responses
     * @param int $limit The number of common words to return
     * @return array
     */
    protected function calculateCommonWords(array $responses, int $limit = 10): array
    {
        if (empty($responses)) {
            return [];
        }
        
        $allText = '';
        $validResponses = 0;
        
        foreach ($responses as $response) {
            if (!isset($response['text']) || empty($response['text'])) {
                continue;
            }
            
            $text = trim($response['text']);
            if (!empty($text)) {
                $allText .= ' ' . $text;
                $validResponses++;
            }
        }
        
        if (empty($allText) || $validResponses === 0) {
            return [];
        }
        
        // Simple word counting for common words (exclude common stop words)
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'of', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'shall', 'should', 'can', 'could', 'may', 'might', 'that', 'this', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'its', 'our', 'their'];
        
        // Convert to lowercase and clean up
        $allText = strtolower($allText);
        $allText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $allText); // Remove punctuation
        
        // Count words
        $words = str_word_count($allText, 1);
        $wordCounts = array_count_values($words);
        
        // Remove stop words
        foreach ($stopWords as $stopWord) {
            unset($wordCounts[$stopWord]);
        }
        
        // Sort by count (descending)
        arsort($wordCounts);
        
        // Take top words
        $commonWords = [];
        $i = 0;
        
        foreach ($wordCounts as $word => $count) {
            if (strlen($word) < 3) continue; // Skip very short words
            
            $commonWords[] = [
                'text' => $word,
                'count' => $count,
                'percentage' => round(($count / $validResponses) * 100, 1),
            ];
            
            $i++;
            if ($i >= $limit) break;
        }
        
        return $commonWords;
    }
} 