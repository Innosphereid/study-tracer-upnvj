<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page loads successfully.
     */
    public function test_login_page_loads_successfully(): void
    {
        // Arrange - nothing to arrange

        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test successful login with username redirects to dashboard.
     */
    public function test_user_can_login_with_username(): void
    {
        // Arrange
        $user = User::factory()->create([
            'username' => 'testuser',
            'role' => 'admin'
        ]);

        // Act
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test successful login with email redirects to dashboard.
     */
    public function test_user_can_login_with_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        // Act
        $response = $this->post('/login', [
            'username' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test superadmin login redirects to superadmin dashboard.
     */
    public function test_superadmin_login_redirects_to_superadmin_dashboard(): void
    {
        // Arrange
        $user = User::factory()->create([
            'username' => 'superadmin',
            'role' => 'superadmin'
        ]);

        // Act
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect('/superadmin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with invalid credentials fails.
     */
    public function test_login_with_invalid_credentials_fails(): void
    {
        // Arrange
        User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test login with non-existent user fails.
     */
    public function test_login_with_non_existent_user_fails(): void
    {
        // Arrange - create no users

        // Act
        $response = $this->post('/login', [
            'username' => 'nonexistent',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test validation rejects empty username.
     */
    public function test_username_is_required(): void
    {
        // Arrange - nothing to arrange

        // Act
        $response = $this->post('/login', [
            'username' => '',
            'password' => 'password',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test validation rejects empty password.
     */
    public function test_password_is_required(): void
    {
        // Arrange - nothing to arrange

        // Act
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => '',
        ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_functionality(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
            'remember' => 'on',
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertAuthenticated();
        $response->assertCookie(auth()->guard()->getRecallerName(), vsprintf('%s|%s|%s', [
            $user->id,
            $user->getRememberToken(),
            $user->password,
        ]));
    }
}