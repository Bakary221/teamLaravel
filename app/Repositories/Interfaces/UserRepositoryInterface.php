<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function find(string $id): ?User;
    public function findByLogin(string $login): ?User;
    public function findByEmail(string $email): ?User;
    public function findByTelephone(string $telephone): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
    public function exists(string $id): bool;
    public function count(): int;
}