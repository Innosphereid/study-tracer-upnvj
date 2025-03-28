<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StoreQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authed users can create questionnaires
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('Validating store questionnaire request', ['data' => $this->all()]);
        
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:questionnaires,slug',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_template' => 'nullable|boolean',
            'settings' => 'nullable',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'nullable|integer|min:0',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.question_type' => 'required|string|in:text,textarea,radio,checkbox,dropdown,rating,date,file,matrix,likert,yes-no,slider,ranking',
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
            'title.required' => 'Judul kuesioner wajib diisi',
            'slug.unique' => 'Slug kuesioner sudah digunakan, silakan gunakan slug lain',
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
        if ($this->has('settings') && is_string($this->settings)) {
            try {
                $settingsData = json_decode($this->settings, true);
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
        if ($this->has('sections') && is_string($this->sections)) {
            try {
                $sectionsData = json_decode($this->sections, true);
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