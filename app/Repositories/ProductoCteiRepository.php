<?php

namespace App\Repositories;

use App\Models\ProductoCtei;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductoCteiRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = ProductoCtei::with(['subtipo.categoria', 'grupoPrincipal', 'autorCorrespondencia']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['subtipo_id'])) {
            $query->porSubtipo($filters['subtipo_id']);
        }

        if (isset($filters['autor_id'])) {
            $query->porAutor($filters['autor_id']);
        }

        if (isset($filters['grupo_id'])) {
            $query->porGrupo($filters['grupo_id']);
        }

        if (isset($filters['semillero_id'])) {
            $query->porSemillero($filters['semillero_id']);
        }

        if (isset($filters['proyecto_id'])) {
            $query->porProyecto($filters['proyecto_id']);
        }

        if (isset($filters['fecha_inicio'])) {
            $query->porFecha($filters['fecha_inicio'], $filters['fecha_fin'] ?? null);
        }

        if (isset($filters['visible_publicamente'])) {
            $query->visiblesPublicamente();
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('codigo_interno', 'like', "%{$search}%")
                  ->orWhere('doi', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('issn', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('fecha_publicacion', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?ProductoCtei
    {
        return ProductoCtei::with([
            'subtipo.categoria',
            'autoresActivos',
            'gruposActivos',
            'semillerosActivos',
            'proyectosActivos',
            'institucionesActivas',
            'financiacionActiva',
            'software'
        ])->find($id);
    }

    public function create(array $data): ProductoCtei
    {
        return ProductoCtei::create($data);
    }

    public function update(int $id, array $data): ProductoCtei
    {
        $producto = ProductoCtei::findOrFail($id);
        $producto->update($data);
        return $producto->fresh();
    }

    public function delete(int $id): bool
    {
        return ProductoCtei::findOrFail($id)->delete();
    }

    public function addAutor(int $productoId, int $userId, int $ordenAutoria = 0, string $rolAutoria = 'Autor', bool $autorCorrespondencia = false): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->autores()->attach($userId, [
            'orden_autoria' => $ordenAutoria,
            'rol_autoria' => $rolAutoria,
            'autor_correspondencia' => $autorCorrespondencia,
            'activo' => true,
        ]);
    }

    public function removeAutor(int $productoId, int $userId): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->autores()->updateExistingPivot($userId, ['activo' => false]);
    }

    public function addGrupo(int $productoId, int $grupoId, bool $grupoPrincipal = false): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->grupos()->attach($grupoId, [
            'grupo_principal' => $grupoPrincipal,
            'activo' => true,
        ]);
    }

    public function removeGrupo(int $productoId, int $grupoId): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->grupos()->updateExistingPivot($grupoId, ['activo' => false]);
    }

    public function addSemillero(int $productoId, int $semilleroId, bool $semilleroPrincipal = false): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->semilleros()->attach($semilleroId, [
            'semillero_principal' => $semilleroPrincipal,
            'activo' => true,
        ]);
    }

    public function removeSemillero(int $productoId, int $semilleroId): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->semilleros()->updateExistingPivot($semilleroId, ['activo' => false]);
    }

    public function addProyecto(int $productoId, int $proyectoId, bool $resultadoPrincipal = false): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->proyectos()->attach($proyectoId, [
            'resultado_principal' => $resultadoPrincipal,
            'activo' => true,
        ]);
    }

    public function removeProyecto(int $productoId, int $proyectoId): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->proyectos()->updateExistingPivot($proyectoId, ['activo' => false]);
    }

    public function addInstitucion(int $productoId, int $institucionId, string $tipoParticipacion = 'Productora', bool $institucionPrincipal = false): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->instituciones()->attach($institucionId, [
            'tipo_participacion' => $tipoParticipacion,
            'institucion_principal' => $institucionPrincipal,
            'activo' => true,
        ]);
    }

    public function removeInstitucion(int $productoId, int $institucionId): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $producto->instituciones()->updateExistingPivot($institucionId, ['activo' => false]);
    }

    public function syncAutores(int $productoId, array $autoresData): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $syncData = [];

        foreach ($autoresData as $autorData) {
            $syncData[$autorData['user_id']] = [
                'orden_autoria' => $autorData['orden_autoria'] ?? 0,
                'rol_autoria' => $autorData['rol_autoria'] ?? 'Autor',
                'autor_correspondencia' => $autorData['autor_correspondencia'] ?? false,
                'activo' => true,
            ];
        }

        $producto->autores()->sync($syncData);
    }

    public function syncGrupos(int $productoId, array $gruposData): void
    {
        $producto = ProductoCtei::findOrFail($productoId);
        $syncData = [];

        foreach ($gruposData as $grupoData) {
            $syncData[$grupoData['grupo_id']] = [
                'grupo_principal' => $grupoData['grupo_principal'] ?? false,
                'activo' => true,
            ];
        }

        $producto->grupos()->sync($syncData);
    }

    public function getProximosAVencer(int $dias = 90): array
    {
        return ProductoCtei::activos()
                           ->whereHas('subtipo', function ($q) {
                               $q->where('ventana_observacion_anios', '>', 0);
                           })
                           ->get()
                           ->filter(function ($producto) use ($dias) {
                               return $producto->estado_ventana === 'proximo_vencer' && 
                                      $producto->subtipo->ventana_observacion_dias <= $dias;
                           })
                           ->values()
                           ->toArray();
    }

    public function getFueraVentana(): array
    {
        return ProductoCtei::activos()
                           ->whereHas('subtipo', function ($q) {
                               $q->where('ventana_observacion_anios', '>', 0);
                           })
                           ->get()
                           ->filter(function ($producto) {
                               return $producto->estado_ventana === 'fuera_ventana';
                           })
                           ->values()
                           ->toArray();
    }

    public function getByEstado(string $estado): array
    {
        return ProductoCtei::porEstado($estado)
                           ->with('subtipo.categoria')
                           ->get()
                           ->toArray();
    }

    public function getBySubtipo(int $subtipoId): array
    {
        return ProductoCtei::porSubtipo($subtipoId)
                           ->with('autores')
                           ->get()
                           ->toArray();
    }

    public function getAllActive(): array
    {
        return ProductoCtei::activos()->with('subtipo.categoria')->get()->toArray();
    }
}
