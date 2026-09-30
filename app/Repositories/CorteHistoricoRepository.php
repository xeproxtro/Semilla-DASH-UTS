<?php

namespace App\Repositories;

use App\Models\CorteHistorico;
use Illuminate\Pagination\LengthAwarePaginator;

class CorteHistoricoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = CorteHistorico::with(['versionCatalogo', 'creadoPor']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['version_catalogo_id'])) {
            $query->porVersionCatalogo($filters['version_catalogo_id']);
        }

        if (isset($filters['fecha_inicio'])) {
            $query->porPeriodo($filters['fecha_inicio'], $filters['fecha_fin'] ?? null);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('fecha_corte', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?CorteHistorico
    {
        return CorteHistorico::with([
            'versionCatalogo',
            'creadoPor',
            'detallesActivos.producto',
            'detallesActivos.subtipo',
            'metricasActivas',
            'alertasActivas'
        ])->find($id);
    }

    public function create(array $data): CorteHistorico
    {
        return CorteHistorico::create($data);
    }

    public function update(int $id, array $data): CorteHistorico
    {
        $corte = CorteHistorico::findOrFail($id);
        $corte->update($data);
        return $corte->fresh();
    }

    public function delete(int $id): bool
    {
        return CorteHistorico::findOrFail($id)->delete();
    }

    public function cerrarCorte(int $id): bool
    {
        $corte = CorteHistorico::findOrFail($id);
        return $corte->update([
            'estado' => 'Cerrado',
            'fecha_congelamiento' => now(),
        ])->estado === 'Cerrado';
    }

    public function reabrirCorte(int $id): CorteHistorico
    {
        $corte = CorteHistorico::findOrFail($id);
        $nuevaVersion = $corte->generarNuevaVersion();
        return CorteHistorico::where('version', $nuevaVersion)->first();
    }

    public function archivarCorte(int $id): bool
    {
        $corte = CorteHistorico::findOrFail($id);
        return $corte->update(['estado' => 'Archivado'])->estado === 'Archivado';
    }

    public function getAbiertos(): array
    {
        return CorteHistorico::abiertos()
                           ->with('creadoPor')
                           ->orderBy('fecha_corte', 'desc')
                           ->get()
                           ->toArray();
    }

    public function getCerrados(): array
    {
        return CorteHistorico::cerrados()
                           ->with('creadoPor')
                           ->orderBy('fecha_corte', 'desc')
                           ->get()
                           ->toArray();
    }

    public function getRecientes(int $dias = 30): array
    {
        return CorteHistorico::recientes($dias)
                           ->with('creadoPor')
                           ->orderBy('fecha_corte', 'desc')
                           ->get()
                           ->toArray();
    }

    public function getUltimoCorte(): ?CorteHistorico
    {
        return CorteHistorico::activos()
                           ->orderBy('fecha_corte', 'desc')
                           ->first();
    }

    public function getByVersionCatalogo(int $versionId): array
    {
        return CorteHistorico::porVersionCatalogo($versionId)
                           ->with('creadoPor')
                           ->orderBy('fecha_corte', 'desc')
                           ->get()
                           ->toArray();
    }
}
