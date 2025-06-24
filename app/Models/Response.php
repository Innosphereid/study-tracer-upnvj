<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'questionnaire_id',
        'user_id',
        'respondent_name',
        'respondent_email',
        'response_data',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'response_data' => 'json',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the questionnaire that this response is for.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Get the user who submitted this response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the answer details for this response.
     */
    public function answerDetails(): HasMany
    {
        return $this->hasMany(AnswerDetail::class);
    }
    
    /**
     * Get a specific answer for a question.
     *
     * @param int $questionId
     * @return AnswerDetail|null
     */
    public function getAnswerForQuestion(int $questionId): ?AnswerDetail
    {
        return $this->answerDetails()->where('question_id', $questionId)->first();
    }

    /**
     * Determine if the response is complete.
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Calculate the time taken to complete the response.
     *
     * @return \Carbon\CarbonInterval|null
     */
    public function timeTaken()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffAsCarbonInterval($this->completed_at);
        }

        return null;
    }
} 