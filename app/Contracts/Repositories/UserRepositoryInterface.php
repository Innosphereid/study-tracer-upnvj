<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a user by email
     *
     * @param string $email
     * @param array $columns
     * @return User|null
     */
    public function findByEmail(string $email, array $columns = ['*']): ?User;
    
    /**
     * Find a user by username
     *
     * @param string $username
     * @param array $columns
     * @return User|null
     */
    public function findByUsername(string $username, array $columns = ['*']): ?User;
    
    /**
     * Get all users with a specific role
     *
     * @param string $role
     * @param array $columns
     * @return Collection
     */
    public function findByRole(string $role, array $columns = ['*']): Collection;
}