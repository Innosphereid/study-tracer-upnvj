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
        'respondent_id',
        'started_at',
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
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the questionnaire that owns the response.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Get the answer data for this response.
     */
    public function answerData(): HasMany
    {
        return $this->hasMany(AnswerData::class);
    }

    /**
     * Check if the response is complete.
     */
    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get answer for a specific question.
     */
    public function getAnswerForQuestion(int $questionId)
    {
        return $this->answerData()->where('question_id', $questionId)->first();
    }

    /**
     * Calculate completion time.
     */
    public function getCompletionTime()
    {
        if (!$this->isComplete() || !$this->started_at) {
            return null;
        }
        
        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Format completion time in human-readable format.
     */
    public function getFormattedCompletionTime(): ?string
    {
        $seconds = $this->getCompletionTime();
        
        if ($seconds === null) {
            return null;
        }
        
        if ($seconds < 60) {
            return $seconds . ' detik';
        }
        
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . ' menit' . ($remainingSeconds > 0 ? ' ' . $remainingSeconds . ' detik' : '');
        }
        
        $hours = floor($seconds / 3600);
        $remainingMinutes = floor(($seconds % 3600) / 60);
        return $hours . ' jam' . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . ' menit' : '');
    }
}