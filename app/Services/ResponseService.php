<?php

namespace App\Services;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Contracts\Services\ResponseServiceInterface;
use App\Models\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ResponseService implements ResponseServiceInterface
{
    /**
     * @var ResponseRepositoryInterface
     */
    protected $responseRepository;
    
    /**
     * @var QuestionnaireRepositoryInterface
     */
    protected $questionnaireRepository;
    
    /**
     * @var AnswerDetailRepositoryInterface
     */
    protected $answerDetailRepository;
    
    /**
     * ResponseService constructor.
     *
     * @param ResponseRepositoryInterface $responseRepository
     * @param QuestionnaireRepositoryInterface $questionnaireRepository
     * @param AnswerDetailRepositoryInterface $answerDetailRepository
     */
    public function __construct(
        ResponseRepositoryInterface $responseRepository,
        QuestionnaireRepositoryInterface $questionnaireRepository,
        AnswerDetailRepositoryInterface $answerDetailRepository
    ) {
        $this->responseRepository = $responseRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->answerDetailRepository = $answerDetailRepository;
    }
    
    /**
     * @inheritDoc
     */
    public function getPaginatedResponses(int $questionnaireId, int $perPage = 10): LengthAwarePaginator
    {
        Log::info('Getting paginated responses', ['questionnaireId' => $questionnaireId, 'perPage' => $perPage]);
        return $this->responseRepository->getPaginatedByQuestionnaire($questionnaireId, $perPage);
    }
    
    /**
     * @inheritDoc
     */
    public function getQuestionnaireResponses(int $questionnaireId): Collection
    {
        Log::info('Getting all responses for questionnaire', ['questionnaireId' => $questionnaireId]);
        return $this->responseRepository->getByQuestionnaireId($questionnaireId);
    }
    
    /**
     * @inheritDoc
     */
    public function getResponseById(int $id): ?Response
    {
        Log::info('Getting response by ID', ['id' => $id]);
        return $this->responseRepository->find($id, ['*'], ['answers']);
    }
    
    /**
     * @inheritDoc
     */
    public function startResponse(int $questionnaireId, array $data): Response
    {
        Log::info('Starting new response', ['questionnaireId' => $questionnaireId]);
        
        // Set questionnaire ID and started_at
        $data['questionnaire_id'] = $questionnaireId;
        $data['started_at'] = now();
        
        // Add IP and user agent if available
        if (!isset($data['ip_address']) && request()->ip()) {
            $data['ip_address'] = request()->ip();
        }
        
        if (!isset($data['user_agent']) && request()->userAgent()) {
            $data['user_agent'] = request()->userAgent();
        }
        
        /** @var Response $response */
        $response = $this->responseRepository->create($data);
        
        Log::info('Response started successfully', ['responseId' => $response->id]);
        
        return $response;
    }
    
    /**
     * @inheritDoc
     */
    public function saveAnswers(int $responseId, array $answers): bool
    {
        Log::info('Saving answers for response', [
            'responseId' => $responseId, 
            'answerCount' => count($answers)
        ]);
        
        // Get response to verify it exists and get questionnaire ID
        $response = $this->responseRepository->find($responseId);
        if (!$response) {
            Log::error('Failed to save answers: response not found', ['responseId' => $responseId]);
            return false;
        }
        
        // Process answers data for structured storage
        $processedAnswers = $this->processAnswersForStorage($answers);
        
        // Continue to update response_data in the Response model for backward compatibility
        $result = $this->responseRepository->saveAnswers($responseId, $processedAnswers);
        
        // Additionally save structured answer details
        $saveDetailResult = $this->answerDetailRepository->saveAnswers(
            $responseId, 
            $processedAnswers, 
            $response->questionnaire_id
        );
        
        if (!$saveDetailResult) {
            Log::warning('Successfully saved answers to response_data but failed to save detailed answers', [
                'responseId' => $responseId
            ]);
        }
        
        return $result;
    }
    
    /**
     * Process different answer types for storage
     * 
     * @param array $answers Raw answers from the request
     * @return array Processed answers ready for storage
     */
    protected function processAnswersForStorage(array $answers): array
    {
        $processed = [];
        
        foreach ($answers as $questionId => $answer) {
            // Skip if question ID is not valid (must be numeric)
            if (!is_numeric($questionId)) {
                Log::warning('Skipping invalid question ID', ['questionId' => $questionId]);
                continue;
            }
            
            // Convert complex answer types to a storable format
            if (is_array($answer)) {
                // Handle specific question types with array answers
                
                // Radio or Dropdown with "other" option
                if (isset($answer['value']) && isset($answer['otherText'])) {
                    if ($answer['value'] === 'other') {
                        // If "other" is selected, use the otherText as the answer
                        $processed[$questionId] = $answer['otherText'];
                    } else {
                        // Otherwise just use the selected value
                        $processed[$questionId] = $answer['value'];
                    }
                    
                    // Keep the full data for detailed storage
                    $processed[$questionId] = [
                        'value' => $answer['value'],
                        'otherText' => $answer['otherText'],
                        '_processedValue' => $processed[$questionId]
                    ];
                    continue;
                }
                
                // Checkbox question
                if (isset($answer['values']) && is_array($answer['values'])) {
                    $processedValue = $answer['values'];
                    
                    // Add otherText if present
                    if (isset($answer['otherText']) && !empty($answer['otherText']) && 
                        in_array('other', $answer['values'])) {
                        $processedValue[] = $answer['otherText'];
                    }
                    
                    $processed[$questionId] = [
                        'values' => $answer['values'],
                        'otherText' => $answer['otherText'] ?? '',
                        '_processedValue' => $processedValue
                    ];
                    continue;
                }
                
                // Matrix question
                if (isset($answer['responses']) || isset($answer['checkboxResponses'])) {
                    // Matrix question (radio or checkbox)
                    if (isset($answer['metadata']) && is_array($answer['metadata'])) {
                        // If metadata is provided, use it directly
                        $processed[$questionId] = [
                            'responses' => $answer['responses'] ?? [],
                            'checkboxResponses' => $answer['checkboxResponses'] ?? [],
                            'metadata' => $answer['metadata'] ?? [],
                            '_processedValue' => json_encode($answer)
                        ];
                    } else {
                        // Create a more readable format with a formatted array of responses
                        $formatted = [];
                        
                        // Format radio responses
                        if (isset($answer['responses']) && is_array($answer['responses'])) {
                            foreach ($answer['responses'] as $rowId => $columnId) {
                                $formatted[] = [
                                    'rowId' => $rowId,
                                    'columnId' => $columnId,
                                    'rowText' => $answer['rowLabels'][$rowId] ?? 'Unknown Row',
                                    'columnText' => $answer['columnLabels'][$columnId] ?? 'Unknown Column',
                                ];
                            }
                        }
                        
                        // Format checkbox responses
                        if (isset($answer['checkboxResponses']) && is_array($answer['checkboxResponses'])) {
                            foreach ($answer['checkboxResponses'] as $rowId => $columnIds) {
                                foreach ($columnIds as $columnId) {
                                    $formatted[] = [
                                        'rowId' => $rowId,
                                        'columnId' => $columnId,
                                        'rowText' => $answer['rowLabels'][$rowId] ?? 'Unknown Row',
                                        'columnText' => $answer['columnLabels'][$columnId] ?? 'Unknown Column',
                                        'type' => 'checkbox'
                                    ];
                                }
                            }
                        }
                        
                        $processed[$questionId] = [
                            'responses' => $answer['responses'] ?? [],
                            'checkboxResponses' => $answer['checkboxResponses'] ?? [],
                            'rowLabels' => $answer['rowLabels'] ?? [],
                            'columnLabels' => $answer['columnLabels'] ?? [],
                            'formatted' => $formatted,
                            '_processedValue' => json_encode($formatted)
                        ];
                    }
                    continue;
                }
                
                // File upload
                if (isset($answer['files']) || isset($answer['fileUrls'])) {
                    $processed[$questionId] = [
                        'files' => $answer['files'] ?? [],
                        'fileUrls' => $answer['fileUrls'] ?? [],
                        '_processedValue' => json_encode($answer)
                    ];
                    continue;
                }
                
                // Ranking question (array of objects with order)
                if (count($answer) > 0 && isset($answer[0]['id']) && isset($answer[0]['order'])) {
                    $processed[$questionId] = [
                        'ranks' => $answer,
                        '_processedValue' => json_encode($answer)
                    ];
                    continue;
                }
                
                // Default: store array as is with JSON encoded version as processedValue
                $processed[$questionId] = [
                    'data' => $answer,
                    '_processedValue' => json_encode($answer)
                ];
            } else {
                // For simple scalar values (text, number, etc.), store as is
                $processed[$questionId] = $answer;
            }
        }
        
        Log::debug('Processed answers for storage', [
            'answersCount' => count($processed)
        ]);
        
        return $processed;
    }
    
    /**
     * @inheritDoc
     */
    public function completeResponse(int $responseId): bool
    {
        Log::info('Completing response', ['responseId' => $responseId]);
        return $this->responseRepository->complete($responseId);
    }
    
    /**
     * @inheritDoc
     */
    public function deleteResponse(int $id): bool
    {
        Log::info('Deleting response', ['id' => $id]);
        return $this->responseRepository->delete($id);
    }
    
    /**
     * @inheritDoc
     */
    public function getResponseAnswers(int $responseId): Collection
    {
        Log::info('Getting answers for response', ['responseId' => $responseId]);
        
        // Try to get answers from answerDetails first
        $answerDetails = $this->answerDetailRepository->getByResponseId($responseId);
        
        if ($answerDetails->isNotEmpty()) {
            Log::info('Retrieved answers from answer_details table', [
                'responseId' => $responseId,
                'count' => $answerDetails->count()
            ]);
            return $answerDetails;
        }
        
        // Fall back to the legacy method if no structured answers found
        Log::info('No structured answers found, falling back to response_data');
        return $this->responseRepository->getAnswers($responseId);
    }
    
    /**
     * @inheritDoc
     */
    public function getQuestionnaireStatistics(int $questionnaireId): array
    {
        Log::info('Getting statistics for questionnaire', ['questionnaireId' => $questionnaireId]);
        return $this->responseRepository->getStatistics($questionnaireId);
    }
    
    /**
     * @inheritDoc
     */
    public function exportResponsesToCSV(int $questionnaireId): string
    {
        Log::info('Exporting responses to CSV', ['questionnaireId' => $questionnaireId]);
        
        $questionnaire = $this->questionnaireRepository->find($questionnaireId, ['*'], ['sections.questions']);
        
        if (!$questionnaire) {
            Log::warning('Failed to export responses: questionnaire not found', ['questionnaireId' => $questionnaireId]);
            return '';
        }
        
        // Get responses without loading the 'answers' relationship
        $responses = $this->responseRepository->getByQuestionnaireId($questionnaireId);
        
        if ($responses->isEmpty()) {
            Log::warning('No responses to export', ['questionnaireId' => $questionnaireId]);
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
                Log::info('Using structured answer details for export', [
                    'responseId' => $response->id,
                    'answerCount' => $answerDetails->count()
                ]);
                
                // Create a map for easy lookup by question ID
                $answerMap = [];
                foreach ($answerDetails as $detail) {
                    $answerMap[$detail->question_id] = $detail;
                }
                
                // Add answers for each question
                foreach ($questions as $questionId => $question) {
                    if (isset($answerMap[$questionId])) {
                        // Default to the simple answer value
                        $value = $answerMap[$questionId]->answer_value;
                        
                        // If we have JSON data, try to format it
                        $jsonData = $answerMap[$questionId]->answer_data;
                        if ($jsonData && is_array($jsonData)) {
                            // Check for the formatted string (for matrix questions)
                            if (isset($jsonData['formatted'])) {
                                $value = $jsonData['formatted'];
                            } 
                            // Check for matrix responses (with readable format)
                            elseif (isset($jsonData['responses']) && isset($jsonData['rowLabels']) && isset($jsonData['columnLabels'])) {
                                $formattedResponses = [];
                                foreach ($jsonData['responses'] as $rowId => $columnId) {
                                    $rowLabel = $jsonData['rowLabels'][$rowId] ?? 'Unknown Row';
                                    $columnLabel = $jsonData['columnLabels'][$columnId] ?? 'Unknown Column';
                                    $formattedResponses[] = "{$rowLabel}: {$columnLabel}";
                                }
                                $value = implode(' | ', $formattedResponses);
                            }
                            // Handle checkbox type questions
                            elseif (isset($jsonData['values']) && is_array($jsonData['values'])) {
                                $value = implode('; ', $jsonData['values']);
                            }
                            // Handle humanReadable format if available
                            elseif (isset($jsonData['humanReadable']) && is_array($jsonData['humanReadable'])) {
                                $value = implode(' | ', $jsonData['humanReadable']);
                            }
                        }
                        
                        $row[] = $value;
                    } else {
                        $row[] = '';
                    }
                }
            } else {
                Log::info('Using legacy response_data for export', ['responseId' => $response->id]);
                
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
        
        Log::info('Successfully exported responses to CSV', [
            'questionnaireId' => $questionnaireId,
            'responseCount' => $responses->count()
        ]);
        
        return $csv;
    }
    
    /**
     * @inheritDoc
     */
    public function findOrCreateResponse(int $questionnaireId, string $identifier, array $data = []): Response
    {
        Log::info('Finding or creating response', [
            'questionnaireId' => $questionnaireId, 
            'identifier' => $identifier
        ]);
        
        // Try to find existing response
        $response = $this->responseRepository->findByRespondentAndQuestionnaire($identifier, $questionnaireId);
        
        if ($response) {
            Log::info('Found existing response', ['responseId' => $response->id]);
            return $response;
        }
        
        // Create new response
        $data['respondent_identifier'] = $identifier;
        return $this->startResponse($questionnaireId, $data);
    }
} 