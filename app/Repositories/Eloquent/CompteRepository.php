<?php

namespace App\Repositories\Eloquent;

use App\Models\Compte;
use App\Repositories\Interfaces\CompteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class CompteRepository implements CompteRepositoryInterface
{
    protected Compte $model;
    protected Builder $query;

    public function __construct(Compte $compte)
    {
        $this->model = $compte;
        $this->query = $compte->newQuery();
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
        return $query->get();
    }

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
        return $query->paginate($perPage);
    }

    public function find(string $id): ?Compte
    {
        return $this->model->find($id);
    }

    public function findOrFail(string $id): Compte
    {
        return $this->model->findOrFail($id);
    }

    public function findByNumero(string $numero): ?Compte
    {
        return $this->model->numero($numero)->first();
    }

    public function create(array $data): Compte
    {
        return $this->model->create($data);
    }

    public function update(Compte $compte, array $data): bool
    {
        return $compte->update($data);
    }

    public function delete(Compte $compte): bool
    {
        return $compte->delete();
    }

    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    public function where(array $conditions): self
    {
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $this->query = $this->query->whereIn($column, $value);
            } else {
                $this->query = $this->query->where($column, $value);
            }
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($column, $direction);
        return $this;
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function first(): ?Compte
    {
        return $this->query->first();
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function exists(string $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Utiliser les nouveaux scopes du CompteScope
        if (isset($filters['statut'])) {
            if ($filters['statut'] === 'actif') {
                $query->actifs();
            } elseif ($filters['statut'] === 'bloqué') {
                $query->bloques();
            } elseif ($filters['statut'] === 'fermé') {
                $query->withFermes()->where('statut', 'fermé');
            }
        }

        if (isset($filters['type'])) {
            $query->duType($filters['type']);
        }

        if (isset($filters['client_id'])) {
            $query->duClient($filters['client_id']);
        }

        if (isset($filters['numero_compte'])) {
            $query->where('numero_compte', 'like', '%' . $filters['numero_compte'] . '%');
        }

        if (isset($filters['avec_solde_positif']) && $filters['avec_solde_positif']) {
            $query->avecSoldePositif();
        }

        if (isset($filters['recents'])) {
            $query->recents($filters['recents']);
        }

        return $query;
    }
}