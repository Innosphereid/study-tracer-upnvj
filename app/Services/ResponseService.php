<?php

namespace App\Services;

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
     * ResponseService constructor.
     *
     * @param ResponseRepositoryInterface $responseRepository
     * @param QuestionnaireRepositoryInterface $questionnaireRepository
     */
    public function __construct(
        ResponseRepositoryInterface $responseRepository,
        QuestionnaireRepositoryInterface $questionnaireRepository
    ) {
        $this->responseRepository = $responseRepository;
        $this->questionnaireRepository = $questionnaireRepository;
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
        Log::info('Saving answers for response', ['responseId' => $responseId, 'answerCount' => count($answers)]);
        return $this->responseRepository->saveAnswers($responseId, $answers);
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
        
        $responses = $this->responseRepository->getByQuestionnaireId($questionnaireId, ['*'], ['answers']);
        
        if ($responses->isEmpty()) {
            Log::warning('No responses to export', ['questionnaireId' => $questionnaireId]);
            return "No responses found\n";
        }
        
        // Prepare headers
        $headers = ['Response ID', 'Respondent', 'Started At', 'Completed At', 'IP Address'];
        
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
                $response->respondent_identifier ?? 'Anonymous',
                $response->started_at ? $response->started_at->format('Y-m-d H:i:s') : '',
                $response->completed_at ? $response->completed_at->format('Y-m-d H:i:s') : '',
                $response->ip_address ?? ''
            ];
            
            // Create a map of answers by question ID for easy access
            $answerMap = [];
            foreach ($response->answers as $answer) {
                $answerMap[$answer->question_id] = $answer;
            }
            
            // Add answers for each question
            foreach ($questions as $questionId => $question) {
                if (isset($answerMap[$questionId])) {
                    $answer = $answerMap[$questionId]->getFormattedAnswer();
                    
                    // Format the answer based on question type
                    if (is_array($answer)) {
                        $row[] = implode('; ', $answer);
                    } else {
                        $row[] = $answer;
                    }
                } else {
                    $row[] = ''; // No answer
                }
            }
            
            // Add row to CSV
            $csv .= implode(',', array_map(function ($cell) {
                return '"' . str_replace('"', '""', $cell) . '"';
            }, $row)) . "\n";
        }
        
        Log::info('CSV export completed', ['questionnaireId' => $questionnaireId, 'rowCount' => $responses->count()]);
        
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