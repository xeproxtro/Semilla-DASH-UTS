<?php

namespace App\Repositories;

use App\Models\SoftwareRegistrado;
use Illuminate\Pagination\LengthAwarePaginator;

class SoftwareRegistradoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = SoftwareRegistrado::with(['producto.subtipo.categoria', 'producto.grupoPrincipal']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (isset($filters['disponibilidad'])) {
            $query->porDisponibilidad($filters['disponibilidad']);
        }

        if (isset($filters['anio'])) {
            $query->porAnio($filters['anio']);
        }

        if (isset($filters['con_certificacion'])) {
            $query->conCertificacion();
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%")
                  ->orWhere('titular', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('anio_desarrollo', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?SoftwareRegistrado
    {
        return SoftwareRegistrado::with([
            'producto.subtipo.categoria',
            'producto.autoresActivos',
            'producto.gruposActivos',
            'fasesActivas.responsable',
            'fasesActivas.documentosActivos',
            'certificacionesActivas'
        ])->find($id);
    }

    public function create(array $data): SoftwareRegistrado
    {
        return SoftwareRegistrado::create($data);
    }

    public function update(int $id, array $data): SoftwareRegistrado
    {
        $software = SoftwareRegistrado::findOrFail($id);
        $software->update($data);
        return $software->fresh();
    }

    public function delete(int $id): bool
    {
        return SoftwareRegistrado::findOrFail($id)->delete();
    }

    public function getByProducto(int $productoId): ?SoftwareRegistrado
    {
        return SoftwareRegistrado::where('producto_id', $productoId)
                                 ->with('fasesActivas')
                                 ->first();
    }

    public function getConCertificacion(): array
    {
        return SoftwareRegistrado::conCertificacion()
                                 ->with('producto')
                                 ->get()
                                 ->toArray();
    }

    public function getPorAnio(int $anio): array
    {
        return SoftwareRegistrado::porAnio($anio)
                                 ->with('producto')
                                 ->get()
                                 ->toArray();
    }

    public function getAllActive(): array
    {
        return SoftwareRegistrado::activos()->with('producto')->get()->toArray();
    }
}
