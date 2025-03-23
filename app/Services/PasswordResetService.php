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
use Illuminate\Support\Facades\Log;
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
            Log::warning('Attempted to send reset link to non-existent email', ['email' => $email]);
            $this->logFailedResetRequest($email, 'Email not found');
            return false;
        }

        Log::info('Generating new OTP for reset password', ['email' => $email]);
        
        // Invalidate old tokens
        $this->resetRepository->invalidateOldTokens($email);

        // Generate new token
        $token = $this->generateOtp();
        $expiresAt = Carbon::now()->addMinutes(10);

        Log::info('OTP generated', [
            'email' => $email, 
            'expires_at' => $expiresAt->toDateTimeString(),
            'token_length' => strlen($token)
        ]);

        // Create token record
        $this->resetRepository->createToken($email, $token, $expiresAt);
        
        Log::info('Token stored in database', ['email' => $email]);

        // Send email with OTP - Force sendNow to bypass queue for testing
        try {
            Log::info('Attempting to send email with OTP', [
                'email' => $email,
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port')
            ]);

            Mail::to($email)->sendNow(new PasswordResetMail($user->name, $token));
            
            // Log successful email sending
            Log::info('Email with OTP sent successfully', ['email' => $email]);
            
            $this->activityLogger->logActivity(
                'password_reset_email_sent',
                "Email reset password berhasil dikirim ke {$email}",
                null,
                request()->ip(),
                request()->userAgent()
            );
            
            return true;
        } catch (\Exception $e) {
            // Log failed email sending with detailed error
            Log::error('Failed to send email with OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->activityLogger->logActivity(
                'password_reset_email_failed',
                "Gagal mengirim email reset password ke {$email}: " . $e->getMessage(),
                null,
                request()->ip(),
                request()->userAgent()
            );
            
            // We'll consider it a failure since the email is critical for OTP delivery
            return false;
        }
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
        $this->activityLogger->logActivity(
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
        try {
            Mail::to($email)->sendNow(new PasswordResetSuccessMail($user->name, now()));
            
            // Log successful email sending
            $this->activityLogger->logActivity(
                'password_reset_success_email_sent',
                "Email konfirmasi reset password berhasil dikirim ke {$email}",
                $user->id,
                request()->ip(),
                request()->userAgent()
            );
        } catch (\Exception $e) {
            // Log failed email sending
            $this->activityLogger->logActivity(
                'password_reset_success_email_failed',
                "Gagal mengirim email konfirmasi reset password ke {$email}: " . $e->getMessage(),
                $user->id,
                request()->ip(),
                request()->userAgent()
            );
            
            Log::error("Failed to send password reset success email: " . $e->getMessage(), [
                'email' => $email,
                'exception' => $e
            ]);
            
            // We'll continue the process even if email fails
        }

        // Log activity
        $this->activityLogger->logActivity(
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
        return sprintf("%06d", mt_rand(0, 999999));
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
        $this->activityLogger->logActivity(
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
        $this->activityLogger->logActivity(
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
        $this->activityLogger->logActivity(
            'password_reset_failed',
            "Reset password gagal untuk email {$email}: {$reason}",
            null,
            request()->ip(),
            request()->userAgent()
        );
    }
}