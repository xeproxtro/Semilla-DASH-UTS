<?php

namespace App\Repositories;

use App\Models\CertificacionInnovacion;
use Illuminate\Pagination\LengthAwarePaginator;

class CertificacionInnovacionRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = CertificacionInnovacion::with(['software']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['entidad'])) {
            $query->porEntidad($filters['entidad']);
        }

        if (isset($filters['nivel'])) {
            $query->porNivel($filters['nivel']);
        }

        if (isset($filters['software_id'])) {
            $query->where('software_id', $filters['software_id']);
        }

        return $query->orderBy('fecha_emision', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?CertificacionInnovacion
    {
        return CertificacionInnovacion::with('software')->find($id);
    }

    public function create(array $data): CertificacionInnovacion
    {
        return CertificacionInnovacion::create($data);
    }

    public function update(int $id, array $data): CertificacionInnovacion
    {
        $certificacion = CertificacionInnovacion::findOrFail($id);
        $certificacion->update($data);
        return $certificacion->fresh();
    }

    public function delete(int $id): bool
    {
        return CertificacionInnovacion::findOrFail($id)->delete();
    }

    public function getBySoftware(int $softwareId): array
    {
        return CertificacionInnovacion::where('software_id', $softwareId)
                                     ->activas()
                                     ->orderBy('fecha_emision', 'desc')
                                     ->get()
                                     ->toArray();
    }

    public function getVigentes(): array
    {
        return CertificacionInnovacion::activas()
                                     ->vigentes()
                                     ->with('software')
                                     ->get()
                                     ->toArray();
    }

    public function getVencidas(): array
    {
        return CertificacionInnovacion::activas()
                                     ->vencidas()
                                     ->with('software')
                                     ->get()
                                     ->toArray();
    }

    public function getPorVencer(int $dias = 30): array
    {
        return CertificacionInnovacion::activas()
                                     ->where('fecha_vigencia', '>=', now())
                                     ->where('fecha_vigencia', '<=', now()->addDays($dias))
                                     ->with('software')
                                     ->get()
                                     ->toArray();
    }
}
