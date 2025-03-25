<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionLogic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'condition_type',
        'condition_value',
        'action_type',
        'action_target',
    ];

    /**
     * The possible condition types.
     */
    public const CONDITION_TYPES = [
        'equals' => 'Sama dengan',
        'not_equals' => 'Tidak sama dengan',
        'contains' => 'Mengandung',
        'not_contains' => 'Tidak mengandung',
        'greater_than' => 'Lebih besar dari',
        'less_than' => 'Lebih kecil dari',
        'is_answered' => 'Terjawab',
        'is_not_answered' => 'Tidak terjawab',
    ];

    /**
     * The possible action types.
     */
    public const ACTION_TYPES = [
        'show' => 'Tampilkan',
        'hide' => 'Sembunyikan',
        'jump' => 'Lompat ke',
    ];

    /**
     * Get the question that owns the logic.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the target question or section based on action_target and action_type.
     */
    public function getActionTarget()
    {
        if ($this->action_type === 'jump') {
            // For jump actions, we could be targeting a question or a section
            $question = Question::find($this->action_target);
            if ($question) {
                return $question;
            }
            
            return Section::find($this->action_target);
        }
        
        // For show/hide actions, we're targeting a question
        return Question::find($this->action_target);
    }
}