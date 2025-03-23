<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class ActivityLoggerServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test logLogin method creates login activity log.
     */
    public function test_log_login_creates_login_activity_log(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $user->username = 'testuser';
        $ipAddress = '127.0.0.1';
        $userAgent = 'Mozilla/5.0';

        // Mock Request facade
        Request::shouldReceive('header')
            ->once()
            ->with('User-Agent')
            ->andReturn($userAgent);

        // Mock ActivityLog model
        $activityLog = Mockery::mock('alias:' . ActivityLog::class);
        $activityLog->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $user->id,
                'event_type' => 'login',
                'username' => $user->username,
                'description' => 'User logged in successfully',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ])
            ->andReturn(new Model());

        $activityLogger = new ActivityLoggerService();

        // Act
        $activityLogger->logLogin($user, $ipAddress);

        // Assert - Verification done through Mockery expectations
    }

    /**
     * Test logLogout method creates logout activity log.
     */
    public function test_log_logout_creates_logout_activity_log(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $user->username = 'testuser';
        $ipAddress = '127.0.0.1';
        $userAgent = 'Mozilla/5.0';

        // Mock Request facade
        Request::shouldReceive('header')
            ->once()
            ->with('User-Agent')
            ->andReturn($userAgent);

        // Mock ActivityLog model
        $activityLog = Mockery::mock('alias:' . ActivityLog::class);
        $activityLog->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $user->id,
                'event_type' => 'logout',
                'username' => $user->username,
                'description' => 'User logged out',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ])
            ->andReturn(new Model());

        $activityLogger = new ActivityLoggerService();

        // Act
        $activityLogger->logLogout($user, $ipAddress);

        // Assert - Verification done through Mockery expectations
    }

    /**
     * Test logFailedLogin method creates failed login activity log.
     */
    public function test_log_failed_login_creates_failed_login_activity_log(): void
    {
        // Arrange
        $username = 'testuser';
        $ipAddress = '127.0.0.1';
        $userAgent = 'Mozilla/5.0';

        // Mock Request facade
        Request::shouldReceive('header')
            ->once()
            ->with('User-Agent')
            ->andReturn($userAgent);

        // Mock ActivityLog model
        $activityLog = Mockery::mock('alias:' . ActivityLog::class);
        $activityLog->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => null,
                'event_type' => 'failed_login',
                'username' => $username,
                'description' => 'Failed login attempt',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ])
            ->andReturn(new Model());

        $activityLogger = new ActivityLoggerService();

        // Act
        $activityLogger->logFailedLogin($username, $ipAddress);

        // Assert - Verification done through Mockery expectations
    }
}