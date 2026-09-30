<?php

namespace App\Repositories;

use App\Models\Semillero;
use Illuminate\Pagination\LengthAwarePaginator;

class SemilleroRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Semillero::with(['coordinador', 'integrantes', 'grupos']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['categoria'])) {
            $query->porCategoria($filters['categoria']);
        }

        if (isset($filters['coordinador_id'])) {
            $query->porCoordinador($filters['coordinador_id']);
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

    public function findById(int $id): ?Semillero
    {
        return Semillero::with(['coordinador', 'integrantesActivos', 'gruposActivos'])
                       ->find($id);
    }

    public function create(array $data): Semillero
    {
        return Semillero::create($data);
    }

    public function update(int $id, array $data): Semillero
    {
        $semillero = Semillero::findOrFail($id);
        $semillero->update($data);
        return $semillero->fresh();
    }

    public function delete(int $id): bool
    {
        return Semillero::findOrFail($id)->delete();
    }

    public function addMember(int $semilleroId, int $userId, string $rol = 'Integrante', ?string $observaciones = null): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $semillero->integrantes()->attach($userId, [
            'rol' => $rol,
            'fecha_ingreso' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeMember(int $semilleroId, int $userId): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $semillero->integrantes()->updateExistingPivot($userId, [
            'fecha_retiro' => now(),
            'activo' => false,
        ]);
    }

    public function syncMembers(int $semilleroId, array $membersData): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $syncData = [];

        foreach ($membersData as $memberData) {
            $syncData[$memberData['user_id']] = [
                'rol' => $memberData['rol'] ?? 'Integrante',
                'fecha_ingreso' => $memberData['fecha_ingreso'] ?? now(),
                'activo' => true,
                'observaciones' => $memberData['observaciones'] ?? null,
            ];
        }

        $semillero->integrantes()->sync($syncData);
    }

    public function articulateWithGrupo(int $semilleroId, int $grupoId, ?string $observaciones = null): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $semillero->grupos()->attach($grupoId, [
            'fecha_articulacion' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeGrupoArticulation(int $semilleroId, int $grupoId): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $semillero->grupos()->updateExistingPivot($grupoId, ['activo' => false]);
    }

    public function syncGrupos(int $semilleroId, array $gruposData): void
    {
        $semillero = Semillero::findOrFail($semilleroId);
        $syncData = [];

        foreach ($gruposData as $grupoData) {
            $syncData[$grupoData['grupo_id']] = [
                'fecha_articulacion' => $grupoData['fecha_articulacion'] ?? now(),
                'activo' => true,
                'observaciones' => $grupoData['observaciones'] ?? null,
            ];
        }

        $semillero->grupos()->sync($syncData);
    }

    public function getAllActive(): array
    {
        return Semillero::activos()->with('coordinador')->get()->toArray();
    }
}
