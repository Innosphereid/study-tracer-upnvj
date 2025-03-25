<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorized via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Debug log
        Log::info('Validating store questionnaire request', [
            'user_id' => auth()->id(),
            'request_data' => $this->all()
        ]);
        
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,closed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_template' => 'nullable|boolean',
            'template_id' => 'nullable|integer|exists:questionnaires,id',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.question_type' => 'required_with:sections.*.questions|string|in:text,textarea,radio,checkbox,dropdown,rating,date,file,matrix,static',
            'sections.*.questions.*.title' => 'required_with:sections.*.questions|string|max:255',
            'sections.*.questions.*.description' => 'nullable|string',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.settings' => 'nullable|array',
            'sections.*.questions.*.options' => 'required_if:sections.*.questions.*.question_type,radio,checkbox,dropdown,matrix|array',
            'sections.*.questions.*.options.*.value' => 'required_with:sections.*.questions.*.options|string',
            'sections.*.questions.*.options.*.label' => 'required_with:sections.*.questions.*.options|string',
            'sections.*.questions.*.logic' => 'nullable|array',
            'sections.*.questions.*.logic.*.condition_type' => 'required_with:sections.*.questions.*.logic|string|in:equals,not_equals,contains,not_contains,greater_than,less_than,is_answered,is_not_answered',
            'sections.*.questions.*.logic.*.condition_value' => 'nullable|string',
            'sections.*.questions.*.logic.*.action_type' => 'required_with:sections.*.questions.*.logic|string|in:show,hide,jump',
            'sections.*.questions.*.logic.*.action_target' => 'required_with:sections.*.questions.*.logic|integer',
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
            'title.required' => 'Judul kuesioner wajib diisi.',
            'title.max' => 'Judul kuesioner maksimal 255 karakter.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
            'sections.*.title.required_with' => 'Judul bagian wajib diisi.',
            'sections.*.questions.*.title.required_with' => 'Judul pertanyaan wajib diisi.',
            'sections.*.questions.*.question_type.in' => 'Tipe pertanyaan tidak valid.',
            'sections.*.questions.*.options.required_if' => 'Pilihan jawaban wajib diisi untuk tipe pertanyaan ini.',
            'sections.*.questions.*.options.*.value.required_with' => 'Nilai pilihan wajib diisi.',
            'sections.*.questions.*.options.*.label.required_with' => 'Label pilihan wajib diisi.',
        ];
    }
}