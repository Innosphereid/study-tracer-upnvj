<?php

namespace App\Contracts\Services;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface AuthenticationServiceInterface
{
    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request): RedirectResponse;

    /**
     * Handle a logout request from the application.
     */
    public function logout(Request $request): RedirectResponse;
}