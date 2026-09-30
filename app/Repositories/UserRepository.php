<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = User::query();

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['rol'])) {
            $query->conRol($filters['rol']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?User
    {
        return User::with(['roles', 'vinculaciones.institucion', 'gruposComoMiembro', 'semillerosComoIntegrante'])
                   ->find($id);
    }

    public function create(array $data): User
    {
        $data['password'] = bcrypt($data['password']);
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        return User::findOrFail($id)->delete();
    }

    public function assignRole(int $userId, int $roleId, ?string $fechaExpiracion = null): void
    {
        $user = User::findOrFail($userId);
        $user->roles()->attach($roleId, [
            'fecha_asignacion' => now(),
            'fecha_expiracion' => $fechaExpiracion,
            'activo' => true,
        ]);
    }

    public function removeRole(int $userId, int $roleId): void
    {
        $user = User::findOrFail($userId);
        $user->roles()->updateExistingPivot($roleId, ['activo' => false]);
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $user = User::findOrFail($userId);
        $user->roles()->sync($roleIds);
    }
}
