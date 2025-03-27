<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PublishQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('Validating publish questionnaire request', [
            'id' => $this->route('questionnaire'),
            'data' => $this->all()
        ]);
        
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'settings' => 'nullable',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'nullable|integer|min:0',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.question_type' => 'required|string',
            'sections.*.questions.*.title' => 'required|string',
            'sections.*.questions.*.description' => 'nullable|string',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.order' => 'nullable|integer|min:0',
            'sections.*.questions.*.settings' => 'nullable',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.options.*.value' => 'nullable|string|max:255',
            'sections.*.questions.*.options.*.label' => 'nullable|string',
            'sections.*.questions.*.options.*.order' => 'nullable|integer|min:0',
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
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
            'sections.*.title.required' => 'Judul bagian wajib diisi',
            'sections.*.questions.*.question_type.required' => 'Tipe pertanyaan wajib diisi',
            'sections.*.questions.*.title.required' => 'Judul pertanyaan wajib diisi',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();
        $modified = false;
        
        // If settings is a string but valid JSON, convert to array
        if ($this->has('settings') && is_string($this->input('settings'))) {
            try {
                $settingsData = json_decode($this->input('settings'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['settings'] = $settingsData;
                    $modified = true;
                    
                    // Extract sections from settings if they exist
                    if (isset($settingsData['sections']) && is_array($settingsData['sections']) && !$this->has('sections')) {
                        $data['sections'] = $settingsData['sections'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error decoding settings JSON', ['error' => $e->getMessage()]);
            }
        }
        
        // Process sections
        if ($this->has('sections') && is_string($this->input('sections'))) {
            try {
                $sectionsData = json_decode($this->input('sections'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['sections'] = $sectionsData;
                    $modified = true;
                }
            } catch (\Exception $e) {
                Log::warning('Error decoding sections JSON', ['error' => $e->getMessage()]);
            }
        }
        
        if ($modified) {
            $this->replace($data);
        }
        
        Log::info('Prepared data for validation', ['data' => $data]);
    }
} 