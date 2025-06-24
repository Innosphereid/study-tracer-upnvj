<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'response_id' => $this->response_id,
            'question_id' => $this->question_id,
            'questionnaire_id' => $this->questionnaire_id,
            'answer_value' => $this->answer_value,
            'answer_data' => $this->answer_data,
            'question' => $this->whenLoaded('question', function () {
                return [
                    'id' => $this->question->id,
                    'title' => $this->question->title,
                    'type' => $this->question->type,
                ];
            }),
            'response' => $this->whenLoaded('response', function () {
                return [
                    'id' => $this->response->id,
                    'respondent_name' => $this->response->respondent_name,
                    'respondent_email' => $this->response->respondent_email,
                    'completed_at' => $this->response->completed_at,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 