<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SubmitResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone can submit a response
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('Validating submit response request', ['slug' => $this->input('slug')]);
        
        return [
            'slug' => 'required|string',
            'respondent_identifier' => 'nullable|string|max:255',
            'answers' => 'required|array',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'slug.required' => 'ID atau slug kuesioner wajib diisi',
            'answers.required' => 'Jawaban wajib diisi',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Process answers if they're in string format
        if ($this->has('answers') && is_string($this->answers)) {
            $this->merge([
                'answers' => json_decode($this->answers, true),
            ]);
        }
        
        // Use IP address as respondent_identifier if not provided
        if (!$this->has('respondent_identifier') || empty($this->respondent_identifier)) {
            $this->merge([
                'respondent_identifier' => $this->ip(),
            ]);
        }
    }
    
    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        if (is_array($validated) && isset($validated['answers'])) {
            // Format answers into the expected structure for saving
            $formattedAnswers = [];
            
            foreach ($validated['answers'] as $questionId => $answer) {
                $formattedAnswers[$questionId] = $answer;
            }
            
            $validated['answers'] = $formattedAnswers;
        }
        
        return $validated;
    }
} 