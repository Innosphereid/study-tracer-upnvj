<?php

namespace App\Contracts\Services;

use App\Models\User;

interface ActivityLoggerInterface
{
    /**
     * Log a user login.
     *
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function logLogin(User $user, string $ipAddress): void;

    /**
     * Log a user logout.
     *
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function logLogout(User $user, string $ipAddress): void;

    /**
     * Log a failed login attempt.
     *
     * @param string $username
     * @param string $ipAddress
     * @param string|null $reason
     * @return void
     */
    public function logFailedLogin(string $username, string $ipAddress, ?string $reason = null): void;

    /**
     * Log a login attempt.
     *
     * @param string $username
     * @param bool $success
     * @param string $ipAddress
     * @return void
     */
    public function logLoginAttempt(string $username, bool $success, string $ipAddress): void;

    /**
     * Log an activity.
     *
     * @param string $eventType
     * @param string $description
     * @param int|null $userId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return void
     */
    public function logActivity(string $eventType, string $description, ?int $userId = null, ?string $ipAddress = null, ?string $userAgent = null): void;
}