<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete login-logout flow with activity logging.
     */
    public function test_complete_login_logout_flow_with_activity_logging(): void
    {
        // Arrange
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        // Act - Part 1: Login
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        // Assert - Part 1: Login successful
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        
        // Assert login activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'login',
            'username' => 'testuser',
        ]);

        // Act - Part 2: Access dashboard
        $dashboardResponse = $this->get('/admin/dashboard');
        
        // Assert - Part 2: Dashboard accessible
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewIs('admin.dashboard');

        // Act - Part 3: Logout
        $logoutResponse = $this->post('/logout');
        
        // Assert - Part 3: Logout successful
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
        
        // Assert logout activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'logout',
            'username' => 'testuser',
        ]);

        // Assert both login and logout were logged (total 2 records)
        $this->assertEquals(2, ActivityLog::count());
    }

    /**
     * Test failed login attempts are logged properly.
     */
    public function test_failed_login_attempts_are_properly_logged(): void
    {
        // Arrange
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        // Act - Failed login attempt
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // Assert - Login failed
        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
        
        // Assert failed login activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'event_type' => 'failed_login',
            'username' => 'testuser',
        ]);
    }

    /**
     * Test rate limiting after multiple failed attempts.
     */
    public function test_rate_limiting_after_multiple_failed_attempts(): void
    {
        // Arrange
        $maxAttempts = 5; // Default rate limit in LoginRequest
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        // Act - Multiple failed login attempts
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post('/login', [
                'username' => 'testuser',
                'password' => 'wrongpassword',
            ]);
        }
        
        // One more attempt that should be rate limited
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // Assert - Rate limited
        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('username'));
        
        // Assert we have exactly maxAttempts + 1 failed login logs
        $this->assertEquals($maxAttempts + 1, ActivityLog::where('event_type', 'failed_login')->count());
    }

    /**
     * Test role-based redirection after login.
     */
    public function test_role_based_redirection_after_login(): void
    {
        // Arrange - Admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Act - Admin login
        $adminResponse = $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
        ]);

        // Assert - Admin redirected to admin dashboard
        $adminResponse->assertRedirect('/admin/dashboard');
        $this->post('/logout');

        // Arrange - Superadmin user
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        // Act - Superadmin login
        $superadminResponse = $this->post('/login', [
            'username' => $superadmin->username,
            'password' => 'password',
        ]);

        // Assert - Superadmin redirected to superadmin dashboard
        $superadminResponse->assertRedirect('/superadmin/dashboard');
    }

    /**
     * Test permissions cascade properly (superadmin can access admin areas).
     */
    public function test_permissions_cascade_properly(): void
    {
        // Arrange
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        
        // Login as superadmin
        $this->post('/login', [
            'username' => $superadmin->username,
            'password' => 'password',
        ]);

        // Act & Assert - Superadmin can access both dashboards
        $this->get('/superadmin/dashboard')->assertStatus(200);
        $this->get('/admin/dashboard')->assertStatus(200);
        
        // Logout
        $this->post('/logout');
        
        // Arrange - Admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        
        // Login as admin
        $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
        ]);

        // Act & Assert - Admin can access admin dashboard but not superadmin dashboard
        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/superadmin/dashboard')->assertRedirect('/admin/dashboard');
    }
}