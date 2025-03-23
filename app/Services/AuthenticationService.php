<?php

namespace App\Services;

use App\Contracts\Services\ActivityLoggerInterface;
use App\Contracts\Services\AuthenticationServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * The activity logger service.
     *
     * @var ActivityLoggerInterface
     */
    protected $activityLogger;

    /**
     * Create a new authentication service instance.
     *
     * @param ActivityLoggerInterface $activityLogger
     */
    public function __construct(ActivityLoggerInterface $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Handle a login request to the application.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
            
            $request->session()->regenerate();

            // Log successful login
            $this->activityLogger->logLogin(Auth::user(), $request->ip());
            
            return $this->redirectBasedOnRole();
        } catch (ValidationException $e) {
            // Log failed login attempt
            $this->activityLogger->logFailedLogin($request->input('username'), $request->ip());
            
            throw $e;
        }
    }

    /**
     * Redirect the user based on their role.
     */
    protected function redirectBasedOnRole(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle a logout request from the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Log logout before actually logging out
        if (Auth::check()) {
            $this->activityLogger->logLogout(Auth::user(), $request->ip());
        }
        
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}