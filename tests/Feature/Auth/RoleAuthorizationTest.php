<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test superadmin can access superadmin dashboard.
     */
    public function test_superadmin_can_access_superadmin_dashboard(): void
    {
        // Arrange
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        $this->actingAs($superadmin);

        // Act
        $response = $this->get('/superadmin/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('superadmin.dashboard');
    }

    /**
     * Test admin cannot access superadmin dashboard.
     */
    public function test_admin_cannot_access_superadmin_dashboard(): void
    {
        // Arrange
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->get('/superadmin/dashboard');

        // Assert
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test admin can access admin dashboard.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        // Arrange
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->get('/admin/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test superadmin can access admin dashboard.
     */
    public function test_superadmin_can_access_admin_dashboard(): void
    {
        // Arrange
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        $this->actingAs($superadmin);

        // Act
        $response = $this->get('/admin/dashboard');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test guest cannot access admin dashboard.
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        // Arrange - make sure we're not authenticated
        $this->assertGuest();

        // Act
        $response = $this->get('/admin/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    /**
     * Test guest cannot access superadmin dashboard.
     */
    public function test_guest_cannot_access_superadmin_dashboard(): void
    {
        // Arrange - make sure we're not authenticated
        $this->assertGuest();

        // Act
        $response = $this->get('/superadmin/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    /**
     * Test root path redirects to login for guest.
     */
    public function test_root_path_redirects_to_login_for_guest(): void
    {
        // Arrange - make sure we're not authenticated
        $this->assertGuest();

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertRedirect('/login');
    }

    /**
     * Test fallback route redirects to appropriate dashboard for authenticated user.
     */
    public function test_fallback_route_redirects_to_appropriate_dashboard_for_authenticated_user(): void
    {
        // Arrange - admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->get('/non-existent-route');

        // Assert
        $response->assertRedirect('/admin/dashboard');

        // Arrange - superadmin user
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        $this->actingAs($superadmin);

        // Act
        $response = $this->get('/non-existent-route');

        // Assert
        $response->assertRedirect('/superadmin/dashboard');
    }

    /**
     * Test fallback route redirects to login for guest.
     */
    public function test_fallback_route_redirects_to_login_for_guest(): void
    {
        // Arrange - make sure we're not authenticated
        $this->assertGuest();

        // Act
        $response = $this->get('/non-existent-route');

        // Assert
        $response->assertRedirect('/login');
    }
}