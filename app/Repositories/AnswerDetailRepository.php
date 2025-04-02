<?php

namespace App\Repositories;

use App\Contracts\Repositories\AnswerDetailRepositoryInterface;
use App\Models\AnswerDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnswerDetailRepository extends BaseRepository implements AnswerDetailRepositoryInterface
{
    /**
     * AnswerDetailRepository constructor.
     *
     * @param AnswerDetail $model
     */
    public function __construct(AnswerDetail $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByResponseId(int $responseId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by response', ['responseId' => $responseId]);
        
        return $this->model->select($columns)
            ->where('response_id', $responseId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionId(int $questionId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by question', ['questionId' => $questionId]);
        
        return $this->model->select($columns)
            ->where('question_id', $questionId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByQuestionnaireId(int $questionnaireId, array $columns = ['*']): Collection
    {
        Log::info('Fetching answer details by questionnaire', ['questionnaireId' => $questionnaireId]);
        
        return $this->model->select($columns)
            ->where('questionnaire_id', $questionnaireId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByResponseAndQuestionId(int $responseId, int $questionId, array $columns = ['*']): ?AnswerDetail
    {
        Log::info('Fetching answer detail by response and question', [
            'responseId' => $responseId,
            'questionId' => $questionId
        ]);
        
        return $this->model->select($columns)
            ->where('response_id', $responseId)
            ->where('question_id', $questionId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function saveAnswers(int $responseId, array $answerData, int $questionnaireId): bool
    {
        Log::info('Saving answer details', [
            'responseId' => $responseId,
            'questionnaireId' => $questionnaireId,
            'answerCount' => count($answerData)
        ]);
        
        return DB::transaction(function () use ($responseId, $answerData, $questionnaireId) {
            $success = true;
            
            foreach ($answerData as $questionId => $answerValue) {
                try {
                    // Check if answer already exists
                    $existingAnswer = $this->getByResponseAndQuestionId($responseId, $questionId);
                    
                    // Prepare answer data
                    $answerValueString = $this->formatAnswerValueForStorage($answerValue);
                    $answerDataJson = $this->formatAnswerDataForStorage($answerValue);
                    
                    if ($existingAnswer) {
                        // Update existing answer
                        $updated = $this->update($existingAnswer->id, [
                            'answer_value' => $answerValueString,
                            'answer_data' => $answerDataJson
                        ]);
                        
                        if (!$updated) {
                            $success = false;
                            Log::error('Failed to update answer detail', [
                                'answerId' => $existingAnswer->id,
                                'questionId' => $questionId
                            ]);
                        }
                    } else {
                        // Create new answer
                        $result = $this->create([
                            'response_id' => $responseId,
                            'question_id' => $questionId,
                            'questionnaire_id' => $questionnaireId,
                            'answer_value' => $answerValueString,
                            'answer_data' => $answerDataJson
                        ]);
                        
                        if (!$result) {
                            $success = false;
                            Log::error('Failed to create answer detail', [
                                'responseId' => $responseId,
                                'questionId' => $questionId
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $success = false;
                    Log::error('Exception while saving answer', [
                        'responseId' => $responseId,
                        'questionId' => $questionId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            return $success;
        });
    }

    /**
     * Format an answer value for storage in the answer_value field
     * 
     * @param mixed $value The answer value to format
     * @return string Formatted string value
     */
    protected function formatAnswerValueForStorage($value): string
    {
        // If it's already processed by ResponseService, use the processed value
        if (is_array($value) && isset($value['_processedValue'])) {
            if (is_array($value['_processedValue'])) {
                return json_encode($value['_processedValue']);
            }
            return (string)$value['_processedValue'];
        }
        
        // Default handling based on type
        if (is_array($value)) {
            return json_encode($value);
        }
        
        // Handle null values
        if ($value === null) {
            return '';
        }
        
        // Convert to string
        return (string)$value;
    }

    /**
     * Format an answer for storage in the answer_data JSON field
     * 
     * @param mixed $value The answer value to format
     * @return array|null Data for JSON storage
     */
    protected function formatAnswerDataForStorage($value): ?array
    {
        // If not an array or is null, wrap in a simple format
        if (!is_array($value)) {
            return ['value' => $value, 'type' => gettype($value)];
        }
        
        // If already has metadata from ResponseService, use as is
        if (isset($value['_processedValue'])) {
            // Remove internal processing marker before storage
            $processedValue = $value;
            unset($processedValue['_processedValue']);
            
            // For Radio/Dropdown questions
            if (isset($processedValue['value']) && isset($processedValue['otherText'])) {
                // Ensure we have a formatted property for consistency
                if (!isset($processedValue['formatted'])) {
                    if ($processedValue['value'] === 'other') {
                        $processedValue['formatted'] = $processedValue['otherText'];
                    } else {
                        $processedValue['formatted'] = $processedValue['value'];
                    }
                }
                
                // Add a humanReadable property for consistency with other question types
                $processedValue['humanReadable'] = $processedValue['formatted'];
            }
            
            // For matrix questions, add a human-readable format
            if (isset($processedValue['responses']) || isset($processedValue['checkboxResponses'])) {
                $readableFormat = [];
                
                // Add a human-readable format for radio responses
                if (isset($processedValue['responses']) && isset($processedValue['rowLabels']) && isset($processedValue['columnLabels'])) {
                    foreach ($processedValue['responses'] as $rowId => $columnId) {
                        $rowLabel = $processedValue['rowLabels'][$rowId] ?? 'Unknown Row';
                        $columnLabel = $processedValue['columnLabels'][$columnId] ?? 'Unknown Column';
                        $readableFormat[] = "{$rowLabel}: {$columnLabel}";
                    }
                }
                
                // Add a human-readable format for checkbox responses
                if (isset($processedValue['checkboxResponses']) && isset($processedValue['rowLabels']) && isset($processedValue['columnLabels'])) {
                    foreach ($processedValue['checkboxResponses'] as $rowId => $columnIds) {
                        $rowLabel = $processedValue['rowLabels'][$rowId] ?? 'Unknown Row';
                        $selections = [];
                        
                        foreach ($columnIds as $columnId) {
                            $columnLabel = $processedValue['columnLabels'][$columnId] ?? 'Unknown Column';
                            $selections[] = $columnLabel;
                        }
                        
                        if (!empty($selections)) {
                            $readableFormat[] = "{$rowLabel}: " . implode(', ', $selections);
                        }
                    }
                }
                
                // Add readable format
                if (!empty($readableFormat)) {
                    $processedValue['humanReadable'] = $readableFormat;
                    $processedValue['formatted'] = implode(' | ', $readableFormat);
                }
            }
            
            return $processedValue;
        }
        
        // Handle radio questions directly if it has the right structure
        if (isset($value['value']) && isset($value['otherText'])) {
            // Create a formatted value
            $formatted = '';
            if ($value['value'] === 'other') {
                $formatted = $value['otherText'];
            } else {
                $formatted = $value['value'];
            }
            
            // Add formatted and humanReadable properties
            $value['formatted'] = $formatted;
            $value['humanReadable'] = $formatted;
        }
        
        // Handle matrix questions directly if it has the right structure
        if (isset($value['responses']) || isset($value['checkboxResponses'])) {
            $readableFormat = [];
            
            // Generate human-readable format for radio responses
            if (isset($value['responses']) && isset($value['rowLabels']) && isset($value['columnLabels'])) {
                foreach ($value['responses'] as $rowId => $columnId) {
                    $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                    $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                    $readableFormat[] = "{$rowLabel}: {$columnLabel}";
                }
            }
            
            // Generate human-readable format for checkbox responses
            if (isset($value['checkboxResponses']) && isset($value['rowLabels']) && isset($value['columnLabels'])) {
                foreach ($value['checkboxResponses'] as $rowId => $columnIds) {
                    $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                    $selections = [];
                    
                    foreach ($columnIds as $columnId) {
                        $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                        $selections[] = $columnLabel;
                    }
                    
                    if (!empty($selections)) {
                        $readableFormat[] = "{$rowLabel}: " . implode(', ', $selections);
                    }
                }
            }
            
            // Add readable format to the value
            if (!empty($readableFormat)) {
                $value['humanReadable'] = $readableFormat;
                $value['formatted'] = implode(' | ', $readableFormat);
            }
        }
        
        // Return array as is
        return $value;
    }
} 