<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Role::query();

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nombre')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Role
    {
        return Role::with('users')->find($id);
    }

    public function findBySlug(string $slug): ?Role
    {
        return Role::where('slug', $slug)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role->fresh();
    }

    public function delete(int $id): bool
    {
        return Role::findOrFail($id)->delete();
    }

    public function getAllActive(): array
    {
        return Role::activos()->get()->toArray();
    }
}
