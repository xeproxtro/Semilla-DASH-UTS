<?php

namespace App\Repositories;

use App\Models\Actividad;
use Illuminate\Pagination\LengthAwarePaginator;

class ActividadRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Actividad::with(['proyecto', 'responsable', 'actividadPadre']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['responsable_id'])) {
            $query->porResponsable($filters['responsable_id']);
        }

        if (isset($filters['proyecto_id'])) {
            $query->porProyecto($filters['proyecto_id']);
        }

        if (isset($filters['raiz'])) {
            $query->raiz();
        }

        return $query->orderBy('fecha_inicio', 'asc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Actividad
    {
        return Actividad::with(['proyecto', 'responsable', 'actividadPadre', 'subactividades', 'avances'])->find($id);
    }

    public function create(array $data): Actividad
    {
        return Actividad::create($data);
    }

    public function update(int $id, array $data): Actividad
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->update($data);
        return $actividad->fresh();
    }

    public function delete(int $id): bool
    {
        return Actividad::findOrFail($id)->delete();
    }

    public function getByProyecto(int $proyectoId): array
    {
        return Actividad::porProyecto($proyectoId)
                        ->activas()
                        ->with('responsable')
                        ->orderBy('fecha_inicio')
                        ->get()
                        ->toArray();
    }

    public function getArbolByProyecto(int $proyectoId): array
    {
        return Actividad::porProyecto($proyectoId)
                        ->activas()
                        ->raiz()
                        ->with('subactividades.subactividades')
                        ->orderBy('fecha_inicio')
                        ->get()
                        ->toArray();
    }

    public function getAtrasadas(int $proyectoId): array
    {
        return Actividad::porProyecto($proyectoId)
                        ->activas()
                        ->where('estado', '!=', 'Completado')
                        ->where('fecha_fin', '<', now())
                        ->with('responsable')
                        ->get()
                        ->toArray();
    }

    public function getPorResponsable(int $responsableId): array
    {
        return Actividad::porResponsable($responsableId)
                        ->activas()
                        ->with('proyecto')
                        ->get()
                        ->toArray();
    }
}
