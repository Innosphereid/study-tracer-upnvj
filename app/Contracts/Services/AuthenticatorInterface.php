<?php

namespace App\Contracts\Services;

use App\Models\User;

interface AuthenticatorInterface
{
    /**
     * Attempt to authenticate a user by username or email and password
     *
     * @param string $usernameOrEmail
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public function attempt(string $usernameOrEmail, string $password, bool $remember = false): bool;
    
    /**
     * Log the user out
     *
     * @return void
     */
    public function logout(): void;
    
    /**
     * Get the currently authenticated user
     *
     * @return User|null
     */
    public function user(): ?User;
    
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function check(): bool;
    
    /**
     * Check if user is guest
     *
     * @return bool
     */
    public function guest(): bool;
}