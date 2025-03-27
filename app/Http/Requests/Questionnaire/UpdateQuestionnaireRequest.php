<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authed users can update questionnaires
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('Validating update questionnaire request', ['id' => $this->route('questionnaire')]);
        
        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('questionnaires', 'slug')->ignore($this->route('questionnaire')),
            ],
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|in:draft,published,closed',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'is_template' => 'sometimes|nullable|boolean',
            'settings' => 'sometimes|nullable|json',
            'sections' => 'sometimes|nullable|array',
            'sections.*.id' => 'nullable|integer|exists:sections,id',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'nullable|integer|min:0',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.id' => 'nullable|integer|exists:questions,id',
            'sections.*.questions.*.question_type' => 'required|string|in:text,textarea,radio,checkbox,dropdown,rating,date,file,matrix',
            'sections.*.questions.*.title' => 'required|string',
            'sections.*.questions.*.description' => 'nullable|string',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.order' => 'nullable|integer|min:0',
            'sections.*.questions.*.settings' => 'nullable|json',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.options.*.id' => 'nullable|integer|exists:options,id',
            'sections.*.questions.*.options.*.value' => 'required|string|max:255',
            'sections.*.questions.*.options.*.label' => 'required|string',
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
            'sections.*.questions.*.question_type.in' => 'Tipe pertanyaan tidak valid',
            'sections.*.questions.*.title.required' => 'Judul pertanyaan wajib diisi',
            'sections.*.questions.*.options.*.value.required' => 'Nilai opsi wajib diisi',
            'sections.*.questions.*.options.*.label.required' => 'Label opsi wajib diisi',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If settings is a string but valid JSON, convert to array
        if ($this->has('settings') && is_string($this->settings)) {
            $this->merge([
                'settings' => $this->settings,
            ]);
        }
        
        // Process sections
        if ($this->has('sections') && is_string($this->sections)) {
            $this->merge([
                'sections' => json_decode($this->sections, true),
            ]);
        }
    }
} 