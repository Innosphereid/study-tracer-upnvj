<?php

namespace App\Contracts\Repositories;

use App\Models\PasswordResetToken;

interface PasswordResetRepositoryInterface
{
    /**
     * Create a new password reset token.
     *
     * @param string $email
     * @param string $token
     * @param \DateTimeInterface $expiresAt
     * @return PasswordResetToken
     */
    public function createToken(string $email, string $token, \DateTimeInterface $expiresAt): PasswordResetToken;

    /**
     * Find the most recent valid token for the given email.
     *
     * @param string $email
     * @return PasswordResetToken|null
     */
    public function findValidToken(string $email): ?PasswordResetToken;

    /**
     * Find token by email and token value.
     *
     * @param string $email
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function findByEmailAndToken(string $email, string $token): ?PasswordResetToken;

    /**
     * Invalidate old tokens for the given email.
     *
     * @param string $email
     * @return void
     */
    public function invalidateOldTokens(string $email): void;

    /**
     * Delete token.
     *
     * @param PasswordResetToken $token
     * @return void
     */
    public function deleteToken(PasswordResetToken $token): void;

    /**
     * Delete all tokens for the given email.
     *
     * @param string $email
     * @return void
     */
    public function deleteAllForEmail(string $email): void;
}