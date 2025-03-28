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
        Log::info('Validating submit response request', ['data' => $this->all()]);
        
        return [
            'slug' => 'required|string',
            'respondent_identifier' => 'nullable|string|max:255',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer_value' => 'nullable',
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
            'answers.required' => 'Jawaban wajib diisi',
            'answers.*.question_id.required' => 'ID pertanyaan wajib diisi',
            'answers.*.question_id.exists' => 'Pertanyaan tidak ditemukan',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Process answers
        if ($this->has('answers') && is_string($this->answers)) {
            $this->merge([
                'answers' => json_decode($this->answers, true),
            ]);
        }
    }
} 