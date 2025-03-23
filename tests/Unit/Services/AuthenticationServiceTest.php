<?php

namespace Tests\Unit\Services;

use App\Contracts\Services\ActivityLoggerInterface;
use App\Contracts\Services\AuthenticatorInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful login and redirection for admin.
     */
    public function test_login_authenticates_and_redirects_admin(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isSuperAdmin')
            ->once()
            ->andReturn(false);

        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        $activityLogger->shouldReceive('logLogin')
            ->once()
            ->with($user, '127.0.0.1');

        $authenticator = Mockery::mock(AuthenticatorInterface::class);
        $authenticator->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('authenticate')
            ->once();
        $request->shouldReceive('ip')
            ->once()
            ->andReturn('127.0.0.1');
        
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('regenerate')
            ->once();
        $request->shouldReceive('session')
            ->once()
            ->andReturn($session);

        $redirectResponse = Mockery::mock(RedirectResponse::class);
        $authService = Mockery::mock(AuthenticationService::class, [$activityLogger, $authenticator])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $authService->shouldReceive('redirectBasedOnRole')
            ->once()
            ->andReturn($redirectResponse);

        // Act
        $result = $authService->login($request);

        // Assert
        $this->assertSame($redirectResponse, $result);
    }

    /**
     * Test login handles validation exception and logs failed attempt.
     */
    public function test_login_handles_validation_exception_and_logs_failed_attempt(): void
    {
        // Arrange
        $exception = ValidationException::withMessages(['username' => 'Invalid credentials']);

        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        $activityLogger->shouldReceive('logFailedLogin')
            ->once()
            ->with('testuser', '127.0.0.1');

        $authenticator = Mockery::mock(AuthenticatorInterface::class);

        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('authenticate')
            ->once()
            ->andThrow($exception);
        $request->shouldReceive('ip')
            ->once()
            ->andReturn('127.0.0.1');
        $request->shouldReceive('input')
            ->with('username')
            ->once()
            ->andReturn('testuser');

        $authService = new AuthenticationService($activityLogger, $authenticator);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $authService->login($request);
    }

    /**
     * Test redirection for superadmin user.
     */
    public function test_redirect_based_on_role_redirects_superadmin_to_superadmin_dashboard(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isSuperAdmin')
            ->once()
            ->andReturn(true);

        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        
        $authenticator = Mockery::mock(AuthenticatorInterface::class);
        $authenticator->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $redirectResponse = Mockery::mock(RedirectResponse::class);
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('intended')
            ->with('/superadmin/dashboard')
            ->andReturn($redirectResponse);
        $url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $url->shouldReceive('route')
            ->with('superadmin.dashboard')
            ->andReturn('/superadmin/dashboard');
        app()->instance('redirect', $redirector);
        app()->instance('url', $url);

        $authService = new AuthenticationService($activityLogger, $authenticator);

        // Act
        $result = $this->invokeMethod($authService, 'redirectBasedOnRole');

        // Assert
        $this->assertSame($redirectResponse, $result);
    }

    /**
     * Test redirection for admin user.
     */
    public function test_redirect_based_on_role_redirects_admin_to_admin_dashboard(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isSuperAdmin')
            ->once()
            ->andReturn(false);

        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        
        $authenticator = Mockery::mock(AuthenticatorInterface::class);
        $authenticator->shouldReceive('user')
            ->once()
            ->andReturn($user);

        $redirectResponse = Mockery::mock(RedirectResponse::class);
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('intended')
            ->with('/admin/dashboard')
            ->andReturn($redirectResponse);
        $url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $url->shouldReceive('route')
            ->with('admin.dashboard')
            ->andReturn('/admin/dashboard');
        app()->instance('redirect', $redirector);
        app()->instance('url', $url);

        $authService = new AuthenticationService($activityLogger, $authenticator);

        // Act
        $result = $this->invokeMethod($authService, 'redirectBasedOnRole');

        // Assert
        $this->assertSame($redirectResponse, $result);
    }

    /**
     * Test logout functionality.
     */
    public function test_logout_logs_out_user_and_invalidates_session(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);

        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        $activityLogger->shouldReceive('logLogout')
            ->once()
            ->with($user, '127.0.0.1');

        $authenticator = Mockery::mock(AuthenticatorInterface::class);
        $authenticator->shouldReceive('check')
            ->once()
            ->andReturn(true);
        $authenticator->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $authenticator->shouldReceive('logout')
            ->once();

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('ip')
            ->once()
            ->andReturn('127.0.0.1');
        
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('invalidate')
            ->once();
        $session->shouldReceive('regenerateToken')
            ->once();
        $request->shouldReceive('session')
            ->twice()
            ->andReturn($session);

        $redirectResponse = Mockery::mock(RedirectResponse::class);
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('route')
            ->with('login')
            ->andReturn($redirectResponse);
        $url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $url->shouldReceive('route')
            ->with('login')
            ->andReturn('/login');
        app()->instance('redirect', $redirector);
        app()->instance('url', $url);

        $authService = new AuthenticationService($activityLogger, $authenticator);

        // Act
        $result = $authService->logout($request);

        // Assert
        $this->assertSame($redirectResponse, $result);
    }

    /**
     * Helper method to invoke private methods for testing.
     *
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}