<?php

namespace App\Repositories;

use App\Contracts\Repositories\ResponseRepositoryInterface;
use App\Models\AnswerData;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResponseRepository implements ResponseRepositoryInterface
{
    /**
     * Get all responses for a questionnaire.
     *
     * @param int $questionnaireId
     * @return Collection
     */
    public function getAllForQuestionnaire(int $questionnaireId): Collection
    {
        return Response::where('questionnaire_id', $questionnaireId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated responses for a questionnaire.
     *
     * @param int $questionnaireId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedForQuestionnaire(int $questionnaireId, int $perPage = 10): LengthAwarePaginator
    {
        return Response::where('questionnaire_id', $questionnaireId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find response by ID.
     *
     * @param int $id
     * @return Response|null
     */
    public function find(int $id): ?Response
    {
        return Response::with('answerData.question')->find($id);
    }

    /**
     * Create a new response.
     *
     * @param array $data
     * @return Response
     */
    public function create(array $data): Response
    {
        $response = Response::create([
            'questionnaire_id' => $data['questionnaire_id'],
            'respondent_id' => $data['respondent_id'] ?? null,
            'started_at' => now(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
        
        return $response;
    }

    /**
     * Update an existing response.
     *
     * @param int $id
     * @param array $data
     * @return Response
     */
    public function update(int $id, array $data): Response
    {
        $response = Response::findOrFail($id);
        
        $response->update([
            'respondent_id' => $data['respondent_id'] ?? $response->respondent_id,
            'completed_at' => $data['completed_at'] ?? $response->completed_at,
        ]);
        
        return $response;
    }

    /**
     * Delete a response.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Response::destroy($id) > 0;
    }

    /**
     * Complete a response.
     *
     * @param int $id
     * @return Response
     */
    public function complete(int $id): Response
    {
        $response = Response::findOrFail($id);
        
        $response->update([
            'completed_at' => now(),
        ]);
        
        return $response;
    }

    /**
     * Save answer data for a response.
     *
     * @param int $responseId
     * @param int $questionId
     * @param string $value
     * @return bool
     */
    public function saveAnswer(int $responseId, int $questionId, string $value): bool
    {
        // Check if an answer already exists
        $existingAnswer = AnswerData::where('response_id', $responseId)
            ->where('question_id', $questionId)
            ->first();
        
        if ($existingAnswer) {
            // Update existing answer
            return $existingAnswer->update(['value' => $value]);
        } else {
            // Create new answer
            $answer = AnswerData::create([
                'response_id' => $responseId,
                'question_id' => $questionId,
                'value' => $value,
            ]);
            
            return $answer->exists;
        }
    }

    /**
     * Get all answers for a response.
     *
     * @param int $responseId
     * @return Collection
     */
    public function getAnswers(int $responseId): Collection
    {
        return AnswerData::where('response_id', $responseId)
            ->with('question')
            ->get();
    }

    /**
     * Get response statistics for a questionnaire.
     *
     * @param int $questionnaireId
     * @return array
     */
    public function getStatistics(int $questionnaireId): array
    {
        // Get questionnaire responses
        $responses = Response::where('questionnaire_id', $questionnaireId)->get();
        
        // Total responses
        $totalResponses = $responses->count();
        
        // Completed responses
        $completedResponses = $responses->filter(function ($response) {
            return $response->completed_at !== null;
        })->count();
        
        // Completion rate
        $completionRate = $totalResponses > 0 
            ? round(($completedResponses / $totalResponses) * 100, 2) 
            : 0;
        
        // Average completion time in seconds
        $completionTimes = $responses->map(function ($response) {
            return $response->getCompletionTime();
        })->filter();
        
        $avgCompletionTime = $completionTimes->isNotEmpty()
            ? round($completionTimes->avg(), 2)
            : 0;
        
        // Get question statistics
        $questionStats = [];
        
        // Get all questions for this questionnaire
        $questions = Question::whereHas('section', function ($query) use ($questionnaireId) {
            $query->where('questionnaire_id', $questionnaireId);
        })->get();
        
        foreach ($questions as $question) {
            $answers = AnswerData::where('question_id', $question->id)
                ->whereIn('response_id', $responses->pluck('id'))
                ->get();
            
            $answerCount = $answers->count();
            $answerRate = $totalResponses > 0 
                ? round(($answerCount / $totalResponses) * 100, 2) 
                : 0;
            
            $questionStats[$question->id] = [
                'question' => $question->title,
                'type' => $question->question_type,
                'answer_count' => $answerCount,
                'answer_rate' => $answerRate,
            ];
            
            // Add type-specific statistics
            if (in_array($question->question_type, ['radio', 'checkbox', 'dropdown'])) {
                $options = $question->options;
                $optionCounts = [];
                
                foreach ($options as $option) {
                    $optionAnswerCount = 0;
                    
                    if ($question->question_type === 'checkbox') {
                        // For checkbox, count how many times this option was selected
                        foreach ($answers as $answer) {
                            $values = json_decode($answer->value, true) ?: [];
                            if (in_array($option->value, $values)) {
                                $optionAnswerCount++;
                            }
                        }
                    } else {
                        // For radio and dropdown, count exact matches
                        $optionAnswerCount = $answers->where('value', $option->value)->count();
                    }
                    
                    $optionPercentage = $answerCount > 0 
                        ? round(($optionAnswerCount / $answerCount) * 100, 2) 
                        : 0;
                    
                    $optionCounts[$option->label] = [
                        'count' => $optionAnswerCount,
                        'percentage' => $optionPercentage,
                    ];
                }
                
                $questionStats[$question->id]['options'] = $optionCounts;
            }
        }
        
        return [
            'total_responses' => $totalResponses,
            'completed_responses' => $completedResponses,
            'completion_rate' => $completionRate,
            'avg_completion_time' => $avgCompletionTime,
            'avg_completion_time_formatted' => $this->formatSeconds($avgCompletionTime),
            'questions' => $questionStats,
        ];
    }
    
    /**
     * Format seconds into a human-readable time string.
     *
     * @param int $seconds
     * @return string
     */
    private function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' detik';
        }
        
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . ' menit' . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . ' detik' : '');
        }
        
        $hours = floor($seconds / 3600);
        $remainingMinutes = floor(($seconds % 3600) / 60);
        return $hours . ' jam' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . ' menit' : '');
    }
}