<?php

namespace App\Repositories;

use App\Models\Evidencia;
use Illuminate\Pagination\LengthAwarePaginator;

class EvidenciaRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Evidencia::with(['producto', 'subidoPor']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['tipo'])) {
            $query->porTipo($filters['tipo']);
        }

        if (isset($filters['producto_id'])) {
            $query->porProducto($filters['producto_id']);
        }

        if (isset($filters['nivel_acceso'])) {
            $query->porNivelAcceso($filters['nivel_acceso']);
        }

        if (isset($filters['subido_por'])) {
            $query->where('subido_por', $filters['subido_por']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Evidencia
    {
        return Evidencia::with(['producto', 'subidoPor'])->find($id);
    }

    public function create(array $data): Evidencia
    {
        return Evidencia::create($data);
    }

    public function update(int $id, array $data): Evidencia
    {
        $evidencia = Evidencia::findOrFail($id);
        $evidencia->update($data);
        return $evidencia->fresh();
    }

    public function delete(int $id): bool
    {
        return Evidencia::findOrFail($id)->delete();
    }

    public function getByProducto(int $productoId): array
    {
        return Evidencia::porProducto($productoId)
                        ->activas()
                        ->with('subidoPor')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->toArray();
    }

    public function getArchivosByProducto(int $productoId): array
    {
        return Evidencia::porProducto($productoId)
                        ->activas()
                        ->archivos()
                        ->get()
                        ->toArray();
    }

    public function getEnlacesByProducto(int $productoId): array
    {
        return Evidencia::porProducto($productoId)
                        ->activas()
                        ->enlaces()
                        ->get()
                        ->toArray();
    }

    public function validarIntegridad(int $id, string $hash): bool
    {
        $evidencia = Evidencia::findOrFail($id);
        return $evidencia->verificarIntegridad($hash);
    }

    public function marcarValidada(int $id): bool
    {
        return Evidencia::findOrFail($id)->update(['validado' => true]);
    }
}
