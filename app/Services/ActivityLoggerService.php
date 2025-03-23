<?php

namespace App\Services;

use App\Contracts\Services\ActivityLoggerInterface;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class ActivityLoggerService implements ActivityLoggerInterface
{
    /**
     * @inheritDoc
     */
    public function logLogin(User $user, string $ipAddress): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'event_type' => 'login',
            'username' => $user->username,
            'description' => 'User logged in successfully',
            'ip_address' => $ipAddress,
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function logLogout(User $user, string $ipAddress): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'event_type' => 'logout',
            'username' => $user->username,
            'description' => 'User logged out',
            'ip_address' => $ipAddress,
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function logFailedLogin(string $username, string $ipAddress, ?string $reason = null): void
    {
        ActivityLog::create([
            'user_id' => null,
            'event_type' => 'login_failed',
            'username' => $username,
            'description' => 'Login failed' . ($reason ? ": {$reason}" : ''),
            'ip_address' => $ipAddress,
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function logLoginAttempt(string $username, bool $success, string $ipAddress): void
    {
        ActivityLog::create([
            'user_id' => null,
            'event_type' => $success ? 'login_success' : 'login_failed',
            'username' => $username,
            'description' => $success ? 'Login attempt successful' : 'Login attempt failed',
            'ip_address' => $ipAddress,
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function logActivity(string $eventType, string $description, ?int $userId = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'username' => $userId ? User::find($userId)?->username : null,
            'description' => $description,
            'ip_address' => $ipAddress ?? Request::ip(),
            'user_agent' => $userAgent ?? Request::userAgent(),
        ]);
    }
}