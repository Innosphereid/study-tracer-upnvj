<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\AuthenticationServiceInterface;
use App\Helpers\SessionManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * The authentication service instance.
     */
    protected $authService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display the login view.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $response = $this->authService->login($request);
            SessionManager::flashSuccess('Login successful. Welcome back!');
            return $response;
        } catch (ValidationException $e) {
            return back()->withErrors([
                'username' => $e->getMessage(),
            ])->withInput($request->except('password'));
        }
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $response = $this->authService->logout($request);
        SessionManager::flashInfo('You have been logged out successfully.');
        return $response;
    }
}