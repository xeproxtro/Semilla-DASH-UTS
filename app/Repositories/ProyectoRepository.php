<?php

namespace App\Repositories;

use App\Models\Proyecto;
use Illuminate\Pagination\LengthAwarePaginator;

class ProyectoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Proyecto::with(['director', 'lineaInvestigacion', 'grupos', 'semilleros', 'instituciones']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['director_id'])) {
            $query->porDirector($filters['director_id']);
        }

        if (isset($filters['linea_id'])) {
            $query->porLinea($filters['linea_id']);
        }

        if (isset($filters['fecha_inicio'])) {
            $query->porFecha($filters['fecha_inicio'], $filters['fecha_fin'] ?? null);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('convocatoria', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('fecha_inicio', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Proyecto
    {
        return Proyecto::with([
            'director',
            'lineaInvestigacion',
            'gruposActivos',
            'semillerosActivos',
            'institucionesActivas',
            'participantesActivos.user',
            'fuentesActivas',
            'actividadesActivas.responsable',
            'riesgosActivos.responsable',
            'avances.reportadoPor',
            'decisiones.tomadaPor',
            'productos'
        ])->find($id);
    }

    public function create(array $data): Proyecto
    {
        return Proyecto::create($data);
    }

    public function update(int $id, array $data): Proyecto
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->update($data);
        return $proyecto->fresh();
    }

    public function delete(int $id): bool
    {
        return Proyecto::findOrFail($id)->delete();
    }

    public function addGrupo(int $proyectoId, int $grupoId, string $rol = 'Principal', ?string $observaciones = null): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->grupos()->attach($grupoId, [
            'rol' => $rol,
            'fecha_asociacion' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeGrupo(int $proyectoId, int $grupoId): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->grupos()->updateExistingPivot($grupoId, ['activo' => false]);
    }

    public function addSemillero(int $proyectoId, int $semilleroId, string $rol = 'Principal', ?string $observaciones = null): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->semilleros()->attach($semilleroId, [
            'rol' => $rol,
            'fecha_asociacion' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeSemillero(int $proyectoId, int $semilleroId): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->semilleros()->updateExistingPivot($semilleroId, ['activo' => false]);
    }

    public function addInstitucion(int $proyectoId, int $institucionId, string $tipoParticipacion = 'Ejecutora', ?string $observaciones = null): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->instituciones()->attach($institucionId, [
            'tipo_participacion' => $tipoParticipacion,
            'fecha_asociacion' => now(),
            'activo' => true,
            'observaciones' => $observaciones,
        ]);
    }

    public function removeInstitucion(int $proyectoId, int $institucionId): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $proyecto->instituciones()->updateExistingPivot($institucionId, ['activo' => false]);
    }

    public function syncGrupos(int $proyectoId, array $gruposData): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $syncData = [];

        foreach ($gruposData as $grupoData) {
            $syncData[$grupoData['grupo_id']] = [
                'rol' => $grupoData['rol'] ?? 'Principal',
                'fecha_asociacion' => $grupoData['fecha_asociacion'] ?? now(),
                'activo' => true,
                'observaciones' => $grupoData['observaciones'] ?? null,
            ];
        }

        $proyecto->grupos()->sync($syncData);
    }

    public function syncSemilleros(int $proyectoId, array $semillerosData): void
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $syncData = [];

        foreach ($semillerosData as $semilleroData) {
            $syncData[$semilleroData['semillero_id']] = [
                'rol' => $semilleroData['rol'] ?? 'Principal',
                'fecha_asociacion' => $semilleroData['fecha_asociacion'] ?? now(),
                'activo' => true,
                'observaciones' => $semilleroData['observaciones'] ?? null,
            ];
        }

        $proyecto->semilleros()->sync($syncData);
    }

    public function getAllActive(): array
    {
        return Proyecto::activos()->with('director')->get()->toArray();
    }

    public function getByEstado(string $estado): array
    {
        return Proyecto::porEstado($estado)->with('director')->get()->toArray();
    }

    public function getByDirector(int $directorId): array
    {
        return Proyecto::porDirector($directorId)->with('grupos')->get()->toArray();
    }
}
