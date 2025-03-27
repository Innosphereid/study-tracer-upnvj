<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'answer_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'response_id',
        'question_id',
        'answer_value',
    ];

    /**
     * The response that this answer belongs to.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * The question that this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the associated option if the question type has options.
     *
     * @return \App\Models\Option|null
     */
    public function getSelectedOption()
    {
        // Only applicable for certain question types
        $question = $this->question;
        if ($question && $question->hasOptions() && !empty($this->answer_value)) {
            return Option::where('question_id', $this->question_id)
                ->where('value', $this->answer_value)
                ->first();
        }

        return null;
    }

    /**
     * Get the formatted answer value based on question type.
     *
     * @return mixed
     */
    public function getFormattedAnswer()
    {
        $question = $this->question;
        if (!$question) {
            return $this->answer_value;
        }

        switch ($question->question_type) {
            case 'checkbox':
                // For checkbox, answer value might be stored as JSON
                return json_decode($this->answer_value, true) ?? $this->answer_value;
            case 'date':
                return $this->answer_value ? date('d/m/Y', strtotime($this->answer_value)) : null;
            case 'radio':
            case 'dropdown':
                $option = $this->getSelectedOption();
                return $option ? $option->label : $this->answer_value;
            default:
                return $this->answer_value;
        }
    }
} 