<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'attempts',
        'used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Check if the token has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if maximum attempts have been reached.
     *
     * @param int $maxAttempts
     * @return bool
     */
    public function hasReachedMaxAttempts(int $maxAttempts = 5): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Increment the attempts count.
     *
     * @return $this
     */
    public function incrementAttempts()
    {
        $this->update(['attempts' => $this->attempts + 1]);

        return $this;
    }

    /**
     * Mark the token as used.
     *
     * @return $this
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);

        return $this;
    }
}