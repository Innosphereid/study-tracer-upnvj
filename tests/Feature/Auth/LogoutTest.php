<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can logout.
     */
    public function test_user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Act
        $response = $this->post('/logout');

        // Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test unauthenticated users are redirected when trying to access logout.
     */
    public function test_unauthenticated_users_cannot_logout(): void
    {
        // Arrange - ensure we're not authenticated
        $this->assertGuest();

        // Act
        $response = $this->post('/logout');

        // Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test session is regenerated on logout.
     */
    public function test_session_is_regenerated_on_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticated();
        $initialSessionId = session()->getId();

        // Act
        $this->post('/logout');

        // Assert
        $this->assertNotEquals($initialSessionId, session()->getId());
        $this->assertGuest();
    }
}