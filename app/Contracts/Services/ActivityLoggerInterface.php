<?php

namespace App\Contracts\Services;

use App\Models\User;

interface ActivityLoggerInterface
{
    /**
     * Log user login activity
     *
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function logLogin(User $user, string $ipAddress): void;
    
    /**
     * Log user logout activity
     *
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function logLogout(User $user, string $ipAddress): void;
    
    /**
     * Log failed login attempt
     *
     * @param string $username
     * @param string $ipAddress
     * @return void
     */
    public function logFailedLogin(string $username, string $ipAddress): void;
}