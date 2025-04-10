<?php

namespace App\Repositories;

use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Models\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResponseRepository extends BaseRepository implements ResponseRepositoryInterface
{
    /**
     * ResponseRepository constructor.
     *
     * @param Response $model
     */
    public function __construct(Response $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*'], array $relations = []): Collection
    {
        Log::info('Fetching responses by questionnaire', ['questionnaireId' => $questionnaireId]);
        
        $query = $this->model->select($columns)
            ->where('questionnaire_id', $questionnaireId);
            
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->latest()->get();
    }

    /**
     * @inheritDoc
     */
    public function getPaginatedByQuestionnaire(int $questionnaireId, int $perPage = 10, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        Log::info('Fetching paginated responses by questionnaire', [
            'questionnaireId' => $questionnaireId,
            'perPage' => $perPage
        ]);
        
        $query = $this->model->select($columns)
            ->where('questionnaire_id', $questionnaireId);
            
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findByRespondentAndQuestionnaire(string $identifier, int $questionnaireId, array $columns = ['*']): ?Response
    {
        Log::info('Finding response by respondent and questionnaire', [
            'identifier' => $identifier,
            'questionnaireId' => $questionnaireId
        ]);
        
        return $this->model->select($columns)
            ->where('respondent_identifier', $identifier)
            ->where('questionnaire_id', $questionnaireId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function getAnswers(int $responseId): Collection
    {
        Log::info('Fetching answers for response', ['responseId' => $responseId]);
        
        /** @var Response|null $response */
        $response = $this->find($responseId);
        
        if (!$response) {
            Log::warning('Response not found when fetching answers', ['responseId' => $responseId]);
            return collect();
        }
        
        return $response->answers()->with('question')->get();
    }

    /**
     * @inheritDoc
     */
    public function saveAnswers(int $responseId, array $answers): bool
    {
        Log::info('Saving answers for response', ['responseId' => $responseId, 'answerCount' => count($answers)]);
        
        /** @var Response|null $response */
        $response = $this->find($responseId);
        
        if (!$response) {
            Log::warning('Response not found when saving answers', ['responseId' => $responseId]);
            return false;
        }
        
        return DB::transaction(function () use ($response, $answers) {
            foreach ($answers as $answer) {
                if (!isset($answer['question_id'])) {
                    Log::warning('Invalid answer data: missing question_id', ['answer' => $answer]);
                    continue;
                }
                
                // Check if answer already exists
                $existingAnswer = $response->answers()
                    ->where('question_id', $answer['question_id'])
                    ->first();
                    
                if ($existingAnswer) {
                    // Update existing answer
                    $existingAnswer->update([
                        'answer_value' => $answer['answer_value'] ?? null
                    ]);
                } else {
                    // Create new answer
                    $response->answers()->create([
                        'question_id' => $answer['question_id'],
                        'answer_value' => $answer['answer_value'] ?? null
                    ]);
                }
            }
            
            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function complete(int $responseId): bool
    {
        Log::info('Marking response as completed', ['responseId' => $responseId]);
        
        return $this->update($responseId, [
            'completed_at' => now()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getStatistics(int $questionnaireId, array $filters = []): array
    {
        Log::info('Generating statistics for questionnaire', [
            'questionnaireId' => $questionnaireId,
            'filters' => $filters
        ]);
        
        $statistics = [];
        
        // Base query for the questionnaire
        $baseQuery = $this->model->where('questionnaire_id', $questionnaireId);
        
        // Apply date filters if present
        if (!empty($filters['start_date'])) {
            $baseQuery->where('created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $baseQuery->where('created_at', '<=', $filters['end_date']);
        }
        
        // Total responses
        $totalResponses = (clone $baseQuery)->count();
        $statistics['total_responses'] = $totalResponses;
        $statistics['has_responses'] = $totalResponses > 0;
        
        // Completed responses
        $completedResponses = (clone $baseQuery)
            ->whereNotNull('completed_at')
            ->count();
            
        $statistics['completed_responses'] = $completedResponses;
        
        // Completion rate
        $statistics['completion_rate'] = $totalResponses > 0 
            ? round(($completedResponses / $totalResponses) * 100, 2) 
            : 0;
        
        // Average time to complete
        $averageTimeSeconds = (clone $baseQuery)
            ->whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as average_time')
            ->value('average_time');
            
        $statistics['average_time_seconds'] = $averageTimeSeconds ? (int) $averageTimeSeconds : 0;
        
        // Responses per day
        $responsesQuery = clone $baseQuery;
        
        // Define the date range for the responses_per_day data
        // If no filters are specified, use the last 30 days by default
        $startDate = !empty($filters['start_date']) 
            ? new \DateTime($filters['start_date']) 
            : now()->subDays(30);
            
        $endDate = !empty($filters['end_date']) 
            ? new \DateTime($filters['end_date']) 
            : now();
        
        $responsesPerDay = $responsesQuery
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
        $statistics['responses_per_day'] = $responsesPerDay;
        
        // Add selected period information to statistics
        $statistics['period'] = [
            'start_date' => !empty($filters['start_date']) ? $filters['start_date'] : null,
            'end_date' => !empty($filters['end_date']) ? $filters['end_date'] : null,
            'type' => !empty($filters['period_type']) ? $filters['period_type'] : 'all'
        ];
        
        return $statistics;
    }
} 