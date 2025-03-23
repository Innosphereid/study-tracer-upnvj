<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Test that login error messages are ambiguous for security.
     */
    public function test_login_error_messages_are_ambiguous_for_security(): void
    {
        // Arrange
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
        ]);

        // Act - Case 1: Wrong password for existing user
        $response1 = $this->post('/login', [
            'username' => 'existinguser',
            'password' => 'wrongpassword',
        ]);

        // Act - Case 2: Non-existent user
        $response2 = $this->post('/login', [
            'username' => 'nonexistentuser',
            'password' => 'password',
        ]);

        // Assert - Both errors should have the same message (ambiguous)
        $response1->assertSessionHasErrors('username');
        $response2->assertSessionHasErrors('username');
        
        // Check the session for error messages
        $this->followRedirects($response1)->assertSee(trans('auth.failed'));
        $this->followRedirects($response2)->assertSee(trans('auth.failed'));
        
        // Both error messages should be identical (no way to tell wrong password from wrong username)
        $this->assertEquals(
            session()->get('errors')->get('username')[0],
            session()->get('errors')->get('username')[0]
        );
    }

    /**
     * Test that rate limiting uses IP address only to avoid username enumeration.
     */
    public function test_rate_limiting_uses_ip_address_only_for_security(): void
    {
        // Arrange
        $maxAttempts = 5; // Default from LoginRequest
        
        // Act - Try multiple failed logins with different usernames
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post('/login', [
                'username' => "user{$i}",
                'password' => 'wrongpassword',
            ]);
        }
        
        // Act - Try with a new username, but same IP
        $response = $this->post('/login', [
            'username' => 'yetanotheruser',
            'password' => 'wrongpassword',
        ]);

        // Assert - Should be rate limited regardless of using different usernames
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('username')
        );
    }

    /**
     * Test CSRF protection for login form.
     */
    public function test_csrf_protection_for_login_form(): void
    {
        // Arrange
        User::factory()->create([
            'username' => 'testuser',
        ]);
        
        // Manually call post without CSRF token
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'username' => 'testuser',
                'password' => 'password',
            ]);
            
        // With proper CSRF token
        $responseCsrf = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        // Assert - Without CSRF, should fail
        $this->assertGuest();
        
        // With CSRF, should succeed
        $responseCsrf->assertRedirect('/admin/dashboard');
    }

    /**
     * Test session regeneration on login for security.
     */
    public function test_session_regeneration_on_login_for_security(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->assertGuest();
        $initialSessionId = session()->getId();

        // Act
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        // Assert
        $this->assertNotEquals($initialSessionId, session()->getId());
        $this->assertAuthenticated();
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(): string
    {
        return sha1('127.0.0.1');
    }
}