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
            'user_agent' => Request::header('User-Agent'),
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
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function logFailedLogin(string $username, string $ipAddress): void
    {
        ActivityLog::create([
            'user_id' => null,
            'event_type' => 'failed_login',
            'username' => $username,
            'description' => 'Failed login attempt',
            'ip_address' => $ipAddress,
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}