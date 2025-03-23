<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\PasswordResetServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetLinkRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $success = $this->resetService->sendResetLinkEmail($email);

        if ($success) {
            return redirect()->route('password.verify-otp-form', ['email' => $email])
                ->with('status', 'Kami telah mengirimkan kode OTP ke alamat email Anda.');
        }

        // Hit rate limiter on failure
        $request->hitRateLimiter();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.']);
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

        $verified = $this->resetService->verifyOtp($email, $otp);

        if ($verified) {
            return redirect()->route('password.reset-form', [
                'token' => $otp,
                'email' => $email
            ]);
        }

        // Hit rate limiter on failure
        $request->hitRateLimiter();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['otp' => 'Kode OTP tidak valid atau telah kedaluwarsa.']);
    }

    /**
     * Display the password reset form.
     *
     * @param string $token
     * @param string $email
     * @return View
     */
    public function showResetForm(string $token, string $email): View
    {
        return view('password.reset', [
            'token' => $token,
            'email' => $email
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

        $reset = $this->resetService->resetPassword($email, $token, $password);

        if ($reset) {
            return redirect()->route('password.success');
        }

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