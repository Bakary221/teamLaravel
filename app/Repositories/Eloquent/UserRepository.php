<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function find(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByLogin(string $login): ?User
    {
        return $this->model->where('login', $login)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByTelephone(string $telephone): ?User
    {
        return $this->model->where('telephone', $telephone)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function exists(string $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function count(): int
    {
        return $this->model->count();
    }
}