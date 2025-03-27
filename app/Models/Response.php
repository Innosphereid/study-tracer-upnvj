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
        'respondent_identifier',
        'ip_address',
        'user_agent',
        'started_at',
        'completed_at',
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
     * The questionnaire that this response belongs to.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * The answers submitted in this response.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(AnswerData::class);
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