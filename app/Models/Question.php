<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_id',
        'question_type',
        'title',
        'description',
        'is_required',
        'order',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * The possible question types.
     */
    public const TYPES = [
        'text' => 'Input Teks Pendek',
        'textarea' => 'Input Teks Panjang',
        'radio' => 'Pilihan Tunggal',
        'checkbox' => 'Pilihan Ganda',
        'dropdown' => 'Dropdown',
        'rating' => 'Rating',
        'date' => 'Tanggal',
        'file' => 'Upload File',
        'matrix' => 'Matriks',
        'static' => 'Teks Statis',
    ];

    /**
     * Get the section that owns the question.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    /**
     * Get the logic rules for this question.
     */
    public function logic(): HasMany
    {
        return $this->hasMany(QuestionLogic::class);
    }

    /**
     * Get the answer data for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(AnswerData::class);
    }

    /**
     * Get settings by key with optional default value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get the next question in this section.
     */
    public function getNextQuestion()
    {
        return Question::where('section_id', $this->section_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get the previous question in this section.
     */
    public function getPreviousQuestion()
    {
        return Question::where('section_id', $this->section_id)
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Check if the question has options.
     */
    public function hasOptions(): bool
    {
        return in_array($this->question_type, ['radio', 'checkbox', 'dropdown', 'matrix']);
    }
}