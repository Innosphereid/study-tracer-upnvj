<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test findByEmail method.
     */
    public function test_find_by_email_returns_user_with_matching_email(): void
    {
        // Arrange
        $email = 'test@example.com';
        $expectedUser = new User();
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('where')
            ->once()
            ->with('email', $email)
            ->andReturnSelf();
        $userModel->shouldReceive('first')
            ->once()
            ->andReturn($expectedUser);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->findByEmail($email);

        // Assert
        $this->assertSame($expectedUser, $result);
    }

    /**
     * Test findByUsername method.
     */
    public function test_find_by_username_returns_user_with_matching_username(): void
    {
        // Arrange
        $username = 'testuser';
        $expectedUser = new User();
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('where')
            ->once()
            ->with('username', $username)
            ->andReturnSelf();
        $userModel->shouldReceive('first')
            ->once()
            ->andReturn($expectedUser);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->findByUsername($username);

        // Assert
        $this->assertSame($expectedUser, $result);
    }

    /**
     * Test findByRole method.
     */
    public function test_find_by_role_returns_users_with_matching_role(): void
    {
        // Arrange
        $role = 'admin';
        $expectedCollection = new Collection([new User(), new User()]);
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('where')
            ->once()
            ->with('role', $role)
            ->andReturnSelf();
        $userModel->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->findByRole($role);

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    /**
     * Test all method.
     */
    public function test_all_returns_all_users(): void
    {
        // Arrange
        $expectedCollection = new Collection([new User(), new User()]);
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('all')
            ->once()
            ->with(['*'])
            ->andReturn($expectedCollection);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->all();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    /**
     * Test find method.
     */
    public function test_find_returns_user_with_matching_id(): void
    {
        // Arrange
        $id = 1;
        $expectedUser = new User();
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($expectedUser);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->find($id);

        // Assert
        $this->assertSame($expectedUser, $result);
    }

    /**
     * Test create method.
     */
    public function test_create_creates_and_returns_new_user(): void
    {
        // Arrange
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123',
            'role' => 'admin'
        ];
        $expectedUser = new User($data);
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedUser);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->create($data);

        // Assert
        $this->assertSame($expectedUser, $result);
    }

    /**
     * Test update method.
     */
    public function test_update_updates_and_returns_true_on_success(): void
    {
        // Arrange
        $id = 1;
        $data = ['name' => 'Updated Name'];
        $user = Mockery::mock(User::class);
        $user->shouldReceive('update')
            ->once()
            ->with($data)
            ->andReturn(true);
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($user);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->update($id, $data);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test update method returns false when user not found.
     */
    public function test_update_returns_false_when_user_not_found(): void
    {
        // Arrange
        $id = 999;
        $data = ['name' => 'Updated Name'];
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturnNull();
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->update($id, $data);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test delete method.
     */
    public function test_delete_deletes_and_returns_true_on_success(): void
    {
        // Arrange
        $id = 1;
        $user = Mockery::mock(User::class);
        $user->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($user);
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->delete($id);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test delete method returns false when user not found.
     */
    public function test_delete_returns_false_when_user_not_found(): void
    {
        // Arrange
        $id = 999;
        
        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('select')
            ->once()
            ->with(['*'])
            ->andReturnSelf();
        $userModel->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturnNull();
            
        $repository = new UserRepository($userModel);

        // Act
        $result = $repository->delete($id);

        // Assert
        $this->assertFalse($result);
    }
}