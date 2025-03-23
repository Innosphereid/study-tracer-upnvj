<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Test the hasRole method.
     */
    public function test_has_role_returns_true_for_matching_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'admin';

        // Act
        $result = $user->hasRole('admin');

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test the hasRole method with non-matching role.
     */
    public function test_has_role_returns_false_for_non_matching_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'admin';

        // Act
        $result = $user->hasRole('superadmin');

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test the isSuperAdmin method.
     */
    public function test_is_super_admin_returns_true_for_superadmin_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'superadmin';

        // Act
        $result = $user->isSuperAdmin();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test the isSuperAdmin method with non-superadmin role.
     */
    public function test_is_super_admin_returns_false_for_non_superadmin_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'admin';

        // Act
        $result = $user->isSuperAdmin();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test the isAdmin method.
     */
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'admin';

        // Act
        $result = $user->isAdmin();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test the isAdmin method with non-admin role.
     */
    public function test_is_admin_returns_false_for_non_admin_role(): void
    {
        // Arrange
        $user = new User();
        $user->role = 'superadmin';

        // Act
        $result = $user->isAdmin();

        // Assert
        $this->assertFalse($result);
    }
}