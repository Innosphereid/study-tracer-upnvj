<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\PasswordResetServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetLinkRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /**
     * The password reset service instance.
     *
     * @var PasswordResetServiceInterface
     */
    protected $resetService;

    /**
     * Create a new controller instance.
     *
     * @param PasswordResetServiceInterface $resetService
     * @return void
     */
    public function __construct(PasswordResetServiceInterface $resetService)
    {
        $this->resetService = $resetService;
        // Middleware adalah tanggung jawab route di Laravel 12, tidak menggunakan $this->middleware() lagi
    }

    /**
     * Display the password reset request form.
     *
     * @return View
     */
    public function showRequestForm(): View
    {
        return view('password.request');
    }

    /**
     * Send a password reset link to the given user.
     *
     * @param SendResetLinkRequest $request
     * @return RedirectResponse
     */
    public function sendResetLink(SendResetLinkRequest $request): RedirectResponse
    {
        // Check rate limiting
        $request->ensureIsNotRateLimited();

        $email = $request->email;
        
        // Log the request
        Log::info('Password reset requested', ['email' => $email, 'ip' => $request->ip()]);
        
        // First check if user exists
        $user = $this->resetService->findUserByEmail($email);
        
        if (!$user) {
            Log::warning('Password reset failed - Email not found', ['email' => $email]);
            
            // Hit rate limiter on failure
            $request->hitRateLimiter();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.']);
        }
        
        // User exists, proceed with sending OTP
        Log::info('User found, sending OTP', ['email' => $email, 'user_id' => $user->id]);
        
        $success = $this->resetService->sendResetLinkEmail($email);

        if ($success) {
            Log::info('OTP sent successfully', ['email' => $email]);
            
            return redirect()->route('password.verify-otp-form', ['email' => $email])
                ->with('status', 'Kami telah mengirimkan kode OTP ke alamat email Anda.');
        }

        // If we get here, something went wrong with sending the email
        Log::error('Failed to send OTP email', ['email' => $email]);
        
        // Hit rate limiter on failure
        $request->hitRateLimiter();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Terjadi kesalahan saat mengirim kode OTP. Silakan coba lagi nanti.']);
    }

    /**
     * Display the OTP verification form.
     *
     * @param Request $request
     * @return View
     */
    public function showVerifyOtpForm(Request $request): View
    {
        // Get email from request query parameter
        $email = $request->email;
        
        if (empty($email)) {
            abort(404, 'Email parameter is required');
        }
        
        return view('password.verify-otp', ['email' => $email]);
    }

    /**
     * Verify the OTP for password reset.
     *
     * @param VerifyOtpRequest $request
     * @return RedirectResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        // Check rate limiting
        $request->ensureIsNotRateLimited();

        $email = $request->email;
        $otp = $request->otp;

        // Log the parameters for debugging
        Log::info('Verifying OTP', [
            'email' => $email,
            'otp' => $otp
        ]);

        $verified = $this->resetService->verifyOtp($email, $otp);

        if ($verified) {
            Log::info('OTP verification successful, redirecting to reset form', [
                'email' => $email,
                'otp' => $otp
            ]);
            
            return redirect()->route('password.reset-form', [
                'token' => $otp,
                'email' => $email
            ]);
        }

        // Hit rate limiter on failure
        $request->hitRateLimiter();

        Log::warning('OTP verification failed', [
            'email' => $email,
            'otp' => $otp
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['otp' => 'Kode OTP tidak valid atau telah kedaluwarsa.']);
    }

    /**
     * Display the password reset form.
     *
     * @param string $token
     * @param Request $request
     * @return View
     */
    public function showResetForm(string $token, Request $request): View
    {
        // Log the parameters for debugging
        Log::info('Showing reset form', [
            'token' => $token,
            'email' => $request->query('email'),
            'all_params' => $request->all()
        ]);
        
        return view('password.reset', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @param ResetPasswordRequest $request
     * @return RedirectResponse
     */
    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $email = $request->email;
        $token = $request->token;
        $password = $request->password;

        // Log the reset attempt
        Log::info('Attempting to reset password', [
            'email' => $email,
            'token_provided' => !empty($token)
        ]);

        $reset = $this->resetService->resetPassword($email, $token, $password);

        if ($reset) {
            Log::info('Password reset successful', ['email' => $email]);
            return redirect()->route('password.success');
        }

        Log::warning('Password reset failed', ['email' => $email]);
        
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
    }

    /**
     * Display the password reset success page.
     *
     * @return View
     */
    public function showSuccessPage(): View
    {
        return view('password.success');
    }

    /**
     * Handle a request to resend the OTP.
     *
     * @param SendResetLinkRequest $request
     * @return RedirectResponse
     */
    public function resendOtp(SendResetLinkRequest $request): RedirectResponse
    {
        // Check rate limiting
        $request->ensureIsNotRateLimited();

        $email = $request->email;
        $success = $this->resetService->sendResetLinkEmail($email);

        if ($success) {
            return back()
                ->with('status', 'Kami telah mengirimkan kode OTP baru ke alamat email Anda.');
        }

        // Hit rate limiter on failure
        $request->hitRateLimiter();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Kami tidak dapat mengirimkan kode OTP baru. Silakan coba lagi nanti.']);
    }
}