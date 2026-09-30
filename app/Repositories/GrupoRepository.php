<?php

namespace App\Repositories;

use App\Models\Grupo;
use Illuminate\Pagination\LengthAwarePaginator;

class GrupoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Grupo::with(['lider', 'miembros', 'lineasInvestigacion']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['categoria'])) {
            $query->porCategoria($filters['categoria']);
        }

        if (isset($filters['lider_id'])) {
            $query->porLider($filters['lider_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nombre')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Grupo
    {
        return Grupo::with(['lider', 'miembrosActivos', 'lineasActivas', 'planesActivos', 'semillerosActivos'])
                   ->find($id);
    }

    public function create(array $data): Grupo
    {
        return Grupo::create($data);
    }

    public function update(int $id, array $data): Grupo
    {
        $grupo = Grupo::findOrFail($id);
        $grupo->update($data);
        return $grupo->fresh();
    }

    public function delete(int $id): bool
    {
        return Grupo::findOrFail($id)->delete();
    }

    public function addMember(int $grupoId, int $userId, string $rol = 'Investigador', ?string $observaciones = null): void
    {
        $grupo = Grupo::findOrFail($grupoId);
        $grupo->miembros()->attach($userId, [
            'rol' => $rol,
            'fecha_ingreso' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeMember(int $grupoId, int $userId): void
    {
        $grupo = Grupo::findOrFail($grupoId);
        $grupo->miembros()->updateExistingPivot($userId, [
            'fecha_retiro' => now(),
            'activo' => false,
        ]);
    }

    public function syncMembers(int $grupoId, array $membersData): void
    {
        $grupo = Grupo::findOrFail($grupoId);
        $syncData = [];

        foreach ($membersData as $memberData) {
            $syncData[$memberData['user_id']] = [
                'rol' => $memberData['rol'] ?? 'Investigador',
                'fecha_ingreso' => $memberData['fecha_ingreso'] ?? now(),
                'activo' => true,
                'observaciones' => $memberData['observaciones'] ?? null,
            ];
        }

        $grupo->miembros()->sync($syncData);
    }

    public function getAllActive(): array
    {
        return Grupo::activos()->with('lider')->get()->toArray();
    }
}
