<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email, array $columns = ['*']): ?User
    {
        return $this->model->select($columns)->where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByUsername(string $username, array $columns = ['*']): ?User
    {
        return $this->model->select($columns)->where('username', $username)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByRole(string $role, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where('role', $role)->get();
    }
}