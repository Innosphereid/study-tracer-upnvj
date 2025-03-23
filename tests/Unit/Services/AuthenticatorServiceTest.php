<?php

namespace Tests\Unit\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\AuthenticatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AuthenticatorServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful authentication with username.
     */
    public function test_attempt_returns_true_with_valid_username_and_password(): void
    {
        // Arrange
        $user = new User();
        $user->username = 'testuser';
        $user->password = Hash::make('password123');

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByUsername')
            ->once()
            ->with('testuser')
            ->andReturn($user);
        
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('login')
            ->once()
            ->with($user, false)
            ->andReturn(true);

        // Act
        $result = $authenticator->attempt('testuser', 'password123', false);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test successful authentication with email.
     */
    public function test_attempt_returns_true_with_valid_email_and_password(): void
    {
        // Arrange
        $user = new User();
        $user->email = 'test@example.com';
        $user->password = Hash::make('password123');

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($user);
        
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('login')
            ->once()
            ->with($user, false)
            ->andReturn(true);

        // Act
        $result = $authenticator->attempt('test@example.com', 'password123', false);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test failed authentication with invalid password.
     */
    public function test_attempt_returns_false_with_invalid_password(): void
    {
        // Arrange
        $user = new User();
        $user->username = 'testuser';
        $user->password = Hash::make('password123');

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByUsername')
            ->once()
            ->with('testuser')
            ->andReturn($user);
        
        $authenticator = new AuthenticatorService($userRepository);

        // Act
        $result = $authenticator->attempt('testuser', 'wrongpassword', false);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test failed authentication with non-existent user.
     */
    public function test_attempt_returns_false_with_non_existent_user(): void
    {
        // Arrange
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByUsername')
            ->once()
            ->with('nonexistent')
            ->andReturnNull();
        
        $authenticator = new AuthenticatorService($userRepository);

        // Act
        $result = $authenticator->attempt('nonexistent', 'password123', false);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test logout functionality.
     */
    public function test_logout_calls_auth_logout(): void
    {
        // Arrange
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('logout')
            ->once();

        // Act
        $authenticator->logout();

        // Assert - verification happens via Mockery expectations
    }

    /**
     * Test user method returns authenticated user.
     */
    public function test_user_returns_authenticated_user(): void
    {
        // Arrange
        $user = new User();
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Act
        $result = $authenticator->user();

        // Assert
        $this->assertSame($user, $result);
    }

    /**
     * Test check method returns authentication status.
     */
    public function test_check_returns_authentication_status(): void
    {
        // Arrange
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        // Act
        $result = $authenticator->check();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test guest method returns guest status.
     */
    public function test_guest_returns_guest_status(): void
    {
        // Arrange
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $authenticator = new AuthenticatorService($userRepository);

        // Mock Auth facade
        Auth::shouldReceive('guest')
            ->once()
            ->andReturn(false);

        // Act
        $result = $authenticator->guest();

        // Assert
        $this->assertFalse($result);
    }
}