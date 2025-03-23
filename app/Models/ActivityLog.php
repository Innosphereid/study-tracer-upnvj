<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_type',
        'username',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * The user associated with this activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}