<?php

namespace App\Contracts\Services;

use App\Models\User;

interface PasswordResetServiceInterface
{
    /**
     * Generate and send a password reset token to the given email.
     *
     * @param string $email
     * @return bool
     */
    public function sendResetLinkEmail(string $email): bool;

    /**
     * Verify the given OTP for the email.
     *
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(string $email, string $otp): bool;

    /**
     * Reset the password for the given user.
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $email, string $token, string $password): bool;

    /**
     * Generate a new OTP token.
     *
     * @return string
     */
    public function generateOtp(): string;

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User;
}