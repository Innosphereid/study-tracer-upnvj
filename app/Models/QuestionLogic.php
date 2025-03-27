<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionLogic extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_logic';

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
     * The question that this logic belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the target question or section based on action_target.
     */
    public function target()
    {
        // Implementation would depend on how action_target is structured
        // This is just a placeholder
        if ($this->action_type === 'jump') {
            return Question::find($this->action_target);
        }
        
        return null;
    }
} 