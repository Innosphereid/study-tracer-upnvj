<?php

namespace App\Services;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Services\ActivityLoggerInterface;
use App\Contracts\Services\PasswordResetServiceInterface;
use App\Mail\PasswordResetMail;
use App\Mail\PasswordResetSuccessMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService implements PasswordResetServiceInterface
{
    /**
     * The repository instance.
     *
     * @var PasswordResetRepositoryInterface
     */
    protected $resetRepository;

    /**
     * The activity logger instance.
     *
     * @var ActivityLoggerInterface
     */
    protected $activityLogger;

    /**
     * Create a new service instance.
     *
     * @param PasswordResetRepositoryInterface $resetRepository
     * @param ActivityLoggerInterface $activityLogger
     * @return void
     */
    public function __construct(
        PasswordResetRepositoryInterface $resetRepository,
        ActivityLoggerInterface $activityLogger
    ) {
        $this->resetRepository = $resetRepository;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Generate and send a password reset token to the given email.
     *
     * @param string $email
     * @return bool
     */
    public function sendResetLinkEmail(string $email): bool
    {
        $user = $this->findUserByEmail($email);

        if (!$user) {
            $this->logFailedResetRequest($email, 'Email not found');
            return false;
        }

        // Invalidate old tokens
        $this->resetRepository->invalidateOldTokens($email);

        // Generate new token
        $token = $this->generateOtp();
        $expiresAt = Carbon::now()->addMinutes(10);

        // Create token record
        $this->resetRepository->createToken($email, $token, $expiresAt);

        // Send email with OTP
        Mail::to($email)->send(new PasswordResetMail($user->name, $token));

        // Log activity
        $this->activityLogger->log(
            'password_reset_request',
            "Permintaan reset password untuk email {$email}",
            null, // No user_id since not authenticated
            request()->ip(),
            request()->userAgent()
        );

        return true;
    }

    /**
     * Verify the given OTP for the email.
     *
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(string $email, string $otp): bool
    {
        $token = $this->resetRepository->findByEmailAndToken($email, $otp);

        if (!$token) {
            $this->logFailedOtpVerification($email, 'Invalid or expired token');
            return false;
        }

        // Increment attempt count
        $token->incrementAttempts();

        // Check if max attempts reached
        if ($token->hasReachedMaxAttempts()) {
            $this->resetRepository->deleteToken($token);
            $this->logFailedOtpVerification($email, 'Max attempts reached');
            return false;
        }

        // Check if token is expired
        if ($token->isExpired()) {
            $this->logFailedOtpVerification($email, 'Token expired');
            return false;
        }

        // Log successful verification
        $this->activityLogger->log(
            'password_reset_otp_verified',
            "Verifikasi OTP untuk reset password berhasil",
            null, // No user_id since not authenticated
            request()->ip(),
            request()->userAgent()
        );

        return true;
    }

    /**
     * Reset the password for the given user.
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $email, string $token, string $password): bool
    {
        $tokenRecord = $this->resetRepository->findByEmailAndToken($email, $token);

        if (!$tokenRecord) {
            $this->logFailedPasswordReset($email, 'Invalid or expired token');
            return false;
        }

        $user = $this->findUserByEmail($email);

        if (!$user) {
            $this->logFailedPasswordReset($email, 'User not found');
            return false;
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Mark token as used
        $tokenRecord->markAsUsed();

        // Send confirmation email
        Mail::to($email)->send(new PasswordResetSuccessMail($user->name, now()));

        // Log activity
        $this->activityLogger->log(
            'password_reset_success',
            "Password berhasil diubah untuk user {$user->username}",
            $user->id,
            request()->ip(),
            request()->userAgent()
        );

        return true;
    }

    /**
     * Generate a new OTP token.
     *
     * @return string
     */
    public function generateOtp(): string
    {
        return mt_rand(100000, 999999);
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Log a failed reset request.
     *
     * @param string $email
     * @param string $reason
     * @return void
     */
    protected function logFailedResetRequest(string $email, string $reason): void
    {
        $this->activityLogger->log(
            'password_reset_request_failed',
            "Reset password gagal untuk email {$email}: {$reason}",
            null,
            request()->ip(),
            request()->userAgent()
        );
    }

    /**
     * Log a failed OTP verification.
     *
     * @param string $email
     * @param string $reason
     * @return void
     */
    protected function logFailedOtpVerification(string $email, string $reason): void
    {
        $this->activityLogger->log(
            'password_reset_otp_failed',
            "Percobaan verifikasi OTP gagal untuk email {$email}: {$reason}",
            null,
            request()->ip(),
            request()->userAgent()
        );
    }

    /**
     * Log a failed password reset.
     *
     * @param string $email
     * @param string $reason
     * @return void
     */
    protected function logFailedPasswordReset(string $email, string $reason): void
    {
        $this->activityLogger->log(
            'password_reset_failed',
            "Reset password gagal untuk email {$email}: {$reason}",
            null,
            request()->ip(),
            request()->userAgent()
        );
    }
}