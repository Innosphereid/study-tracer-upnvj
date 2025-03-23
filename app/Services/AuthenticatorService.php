<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthenticatorInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatorService implements AuthenticatorInterface
{
    /**
     * The user repository instance.
     */
    protected $userRepository;

    /**
     * Create a new authenticator service instance.
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function attempt(string $usernameOrEmail, string $password, bool $remember = false): bool
    {
        // Check if it's an email
        $isEmail = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';
        
        // Find the user
        $user = $isEmail 
            ? $this->userRepository->findByEmail($usernameOrEmail) 
            : $this->userRepository->findByUsername($usernameOrEmail);
        
        // Check if user exists and password matches
        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }
        
        // Log the user in
        Auth::login($user, $remember);
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * @inheritDoc
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return Auth::check();
    }

    /**
     * @inheritDoc
     */
    public function guest(): bool
    {
        return Auth::guest();
    }
}