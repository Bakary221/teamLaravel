<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Interfaces\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    protected Client $model;

    public function __construct(Client $client)
    {
        $this->model = $client;
    }

    public function find(string $id): ?Client
    {
        return $this->model->find($id);
    }

    public function findByUserId(string $userId): ?Client
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function create(array $data): Client
    {
        return $this->model->create($data);
    }

    public function update(Client $client, array $data): bool
    {
        return $client->update($data);
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
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