<?php

namespace App\Repositories\Interfaces;

use App\Models\Compte;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CompteRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function find(string $id): ?Compte;
    public function findOrFail(string $id): Compte;
    public function findByNumero(string $numero): ?Compte;
    public function create(array $data): Compte;
    public function update(Compte $compte, array $data): bool;
    public function delete(Compte $compte): bool;
    public function with(array $relations): self;
    public function where(array $conditions): self;
    public function orderBy(string $column, string $direction = 'asc'): self;
    public function get(): Collection;
    public function first(): ?Compte;
    public function count(): int;
    public function exists(string $id): bool;
}