<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'questionnaire_id',
        'title',
        'description',
        'order',
    ];

    /**
     * Get the questionnaire that owns the section.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Get the questions for this section.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get the next section in this questionnaire.
     */
    public function getNextSection()
    {
        return Section::where('questionnaire_id', $this->questionnaire_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get the previous section in this questionnaire.
     */
    public function getPreviousSection()
    {
        return Section::where('questionnaire_id', $this->questionnaire_id)
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }
}