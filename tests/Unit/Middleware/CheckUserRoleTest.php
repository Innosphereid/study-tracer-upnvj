<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRoleTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test middleware allows access to user with correct role.
     */
    public function test_middleware_allows_access_when_user_has_required_role(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->role = 'admin';

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);
        
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $request = Mockery::mock(Request::class);
        $next = function ($request) {
            return new Response('next middleware called');
        };

        $middleware = new CheckUserRole();

        // Act
        $response = $middleware->handle($request, $next, 'admin');

        // Assert
        $this->assertEquals('next middleware called', $response->getContent());
    }

    /**
     * Test middleware allows access to user with one of multiple roles.
     */
    public function test_middleware_allows_access_when_user_has_one_of_multiple_roles(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->role = 'superadmin';

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);
        
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $request = Mockery::mock(Request::class);
        $next = function ($request) {
            return new Response('next middleware called');
        };

        $middleware = new CheckUserRole();

        // Act
        $response = $middleware->handle($request, $next, 'admin', 'superadmin');

        // Assert
        $this->assertEquals('next middleware called', $response->getContent());
    }

    /**
     * Test middleware redirects when user doesn't have required role.
     */
    public function test_middleware_redirects_when_user_does_not_have_required_role(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->role = 'admin';
        
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);
        
        Auth::shouldReceive('user')
            ->twice()
            ->andReturn($user);

        $request = Mockery::mock(Request::class);
        $next = function ($request) {
            return new Response('next middleware called');
        };

        $middleware = new CheckUserRole();

        // Mock redirect response
        $redirectResponse = Mockery::mock('Illuminate\Http\RedirectResponse');
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('route')
            ->with('admin.dashboard')
            ->andReturn($redirectResponse);
        app()->instance('redirect', $redirector);

        // Act
        $response = $middleware->handle($request, $next, 'superadmin');

        // Assert
        $this->assertSame($redirectResponse, $response);
    }

    /**
     * Test middleware redirects guest to login.
     */
    public function test_middleware_redirects_guest_to_login(): void
    {
        // Arrange
        Auth::shouldReceive('check')
            ->once()
            ->andReturn(false);

        $request = Mockery::mock(Request::class);
        $next = function ($request) {
            return new Response('next middleware called');
        };

        $middleware = new CheckUserRole();

        // Mock redirect response
        $redirectResponse = Mockery::mock('Illuminate\Http\RedirectResponse');
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('route')
            ->with('login')
            ->andReturn($redirectResponse);
        app()->instance('redirect', $redirector);

        // Act
        $response = $middleware->handle($request, $next, 'superadmin');

        // Assert
        $this->assertSame($redirectResponse, $response);
    }

    /**
     * Test redirect for superadmin.
     */
    public function test_middleware_redirects_to_superadmin_dashboard_for_superadmin(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->role = 'superadmin';

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);
        
        Auth::shouldReceive('user')
            ->twice()
            ->andReturn($user);

        $request = Mockery::mock(Request::class);
        $next = function ($request) {
            return new Response('next middleware called');
        };

        $middleware = new CheckUserRole();

        // Mock redirect response
        $redirectResponse = Mockery::mock('Illuminate\Http\RedirectResponse');
        $redirector = Mockery::mock('Illuminate\Routing\Redirector');
        $redirector->shouldReceive('route')
            ->with('superadmin.dashboard')
            ->andReturn($redirectResponse);
        app()->instance('redirect', $redirector);

        // Act
        $response = $middleware->handle($request, $next, 'admin');

        // Assert
        $this->assertSame($redirectResponse, $response);
    }
}