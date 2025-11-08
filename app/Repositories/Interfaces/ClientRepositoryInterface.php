<?php

namespace App\Repositories\Interfaces;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    public function find(string $id): ?Client;
    public function findByUserId(string $userId): ?Client;
    public function create(array $data): Client;
    public function update(Client $client, array $data): bool;
    public function delete(Client $client): bool;
    public function exists(string $id): bool;
    public function count(): int;
}