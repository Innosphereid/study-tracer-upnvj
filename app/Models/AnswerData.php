<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'response_id',
        'question_id',
        'value',
    ];

    /**
     * Get the response that owns the answer data.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * Get the question that owns the answer data.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get formatted value based on question type.
     */
    public function getFormattedValue()
    {
        if (!$this->question) {
            return $this->value;
        }
        
        switch ($this->question->question_type) {
            case 'radio':
            case 'dropdown':
                $option = $this->question->options()->where('value', $this->value)->first();
                return $option ? $option->label : $this->value;
                
            case 'checkbox':
                $values = json_decode($this->value, true) ?: [];
                $options = $this->question->options()
                    ->whereIn('value', $values)
                    ->pluck('label', 'value')
                    ->toArray();
                    
                $formatted = [];
                foreach ($values as $value) {
                    $formatted[] = $options[$value] ?? $value;
                }
                
                return implode(', ', $formatted);
                
            case 'date':
                return $this->value ? date('d/m/Y', strtotime($this->value)) : '';
                
            default:
                return $this->value;
        }
    }
}