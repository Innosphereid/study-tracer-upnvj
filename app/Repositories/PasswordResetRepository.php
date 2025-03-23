<?php

namespace App\Repositories;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    /**
     * Create a new password reset token.
     *
     * @param string $email
     * @param string $token
     * @param \DateTimeInterface $expiresAt
     * @return PasswordResetToken
     */
    public function createToken(string $email, string $token, \DateTimeInterface $expiresAt): PasswordResetToken
    {
        return PasswordResetToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'used' => false,
        ]);
    }

    /**
     * Find the most recent valid token for the given email.
     *
     * @param string $email
     * @return PasswordResetToken|null
     */
    public function findValidToken(string $email): ?PasswordResetToken
    {
        return PasswordResetToken::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Find token by email and token value.
     *
     * @param string $email
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function findByEmailAndToken(string $email, string $token): ?PasswordResetToken
    {
        return PasswordResetToken::where('email', $email)
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Invalidate old tokens for the given email.
     *
     * @param string $email
     * @return void
     */
    public function invalidateOldTokens(string $email): void
    {
        PasswordResetToken::where('email', $email)
            ->where('used', false)
            ->update(['used' => true]);
    }

    /**
     * Delete token.
     *
     * @param PasswordResetToken $token
     * @return void
     */
    public function deleteToken(PasswordResetToken $token): void
    {
        $token->delete();
    }

    /**
     * Delete all tokens for the given email.
     *
     * @param string $email
     * @return void
     */
    public function deleteAllForEmail(string $email): void
    {
        PasswordResetToken::where('email', $email)->delete();
    }
}