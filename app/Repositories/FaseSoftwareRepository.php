<?php

namespace App\Repositories;

use App\Models\FaseSoftware;
use Illuminate\Pagination\LengthAwarePaginator;

class FaseSoftwareRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = FaseSoftware::with(['software', 'responsable']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['fase'])) {
            $query->porFase($filters['fase']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['software_id'])) {
            $query->porSoftware($filters['software_id']);
        }

        return $query->orderBy('fecha_inicio', 'asc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?FaseSoftware
    {
        return FaseSoftware::with(['software', 'responsable', 'documentosActivos'])->find($id);
    }

    public function findBySoftwareAndFase(int $softwareId, string $fase): ?FaseSoftware
    {
        return FaseSoftware::where('software_id', $softwareId)
                          ->where('fase', $fase)
                          ->where('activo', true)
                          ->first();
    }

    public function create(array $data): FaseSoftware
    {
        return FaseSoftware::create($data);
    }

    public function update(int $id, array $data): FaseSoftware
    {
        $fase = FaseSoftware::findOrFail($id);
        $fase->update($data);
        return $fase->fresh();
    }

    public function delete(int $id): bool
    {
        return FaseSoftware::findOrFail($id)->delete();
    }

    public function getBySoftware(int $softwareId): array
    {
        return FaseSoftware::porSoftware($softwareId)
                          ->activas()
                          ->with('responsable')
                          ->orderBy('fase')
                          ->get()
                          ->toArray();
    }

    public function getCompletadasBySoftware(int $softwareId): array
    {
        return FaseSoftware::porSoftware($softwareId)
                          ->activas()
                          ->completadas()
                          ->get()
                          ->toArray();
    }

    public function getPendientesBySoftware(int $softwareId): array
    {
        return FaseSoftware::porSoftware($softwareId)
                          ->activas()
                          ->pendientes()
                          ->get()
                          ->toArray();
    }
}
