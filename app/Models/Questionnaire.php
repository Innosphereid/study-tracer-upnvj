<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'is_template',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_template' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * Get the user who created the questionnaire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sections for the questionnaire.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    /**
     * Get the responses for the questionnaire.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Determine if the questionnaire is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        // If there's no start_date or end_date, it's always active if published
        if (!$this->start_date && !$this->end_date) {
            return true;
        }

        // If there's a start_date but no end_date, it's active after the start_date
        if ($this->start_date && !$this->end_date) {
            return $now->gte($this->start_date);
        }

        // If there's an end_date but no start_date, it's active until the end_date
        if (!$this->start_date && $this->end_date) {
            return $now->lte($this->end_date);
        }

        // If there's both a start_date and an end_date, it's active between the two
        return $now->gte($this->start_date) && $now->lte($this->end_date);
    }

    /**
     * Get all questions in the questionnaire (across all sections).
     */
    public function questions()
    {
        return $this->hasManyThrough(Question::class, Section::class);
    }
} 