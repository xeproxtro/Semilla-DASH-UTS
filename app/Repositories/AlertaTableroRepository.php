<?php

namespace App\Repositories;

use App\Models\AlertaTablero;
use Illuminate\Pagination\LengthAwarePaginator;

class AlertaTableroRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = AlertaTablero::with(['producto', 'grupo', 'semillero', 'asignadoA']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['tipo_alerta'])) {
            $query->porTipo($filters['tipo_alerta']);
        }

        if (isset($filters['prioridad'])) {
            $query->porPrioridad($filters['prioridad']);
        }

        if (isset($filters['corte_id'])) {
            $query->porCorte($filters['corte_id']);
        }

        if (isset($filters['producto_id'])) {
            $query->porProducto($filters['producto_id']);
        }

        if (isset($filters['grupo_id'])) {
            $query->porGrupo($filters['grupo_id']);
        }

        if (isset($filters['semillero_id'])) {
            $query->porSemillero($filters['semillero_id']);
        }

        if (isset($filters['resuelta'])) {
            $query->where('resuelta', $filters['resuelta']);
        }

        return $query->orderBy('fecha_alerta', 'asc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?AlertaTablero
    {
        return AlertaTablero::with(['producto', 'grupo', 'semillero', 'asignadoA'])->find($id);
    }

    public function create(array $data): AlertaTablero
    {
        return AlertaTablero::create($data);
    }

    public function update(int $id, array $data): AlertaTablero
    {
        $alerta = AlertaTablero::findOrFail($id);
        $alerta->update($data);
        return $alerta->fresh();
    }

    public function delete(int $id): bool
    {
        return AlertaTablero::findOrFail($id)->delete();
    }

    public function marcarResuelta(int $id, ?string $observaciones = null): bool
    {
        $alerta = AlertaTablero::findOrFail($id);
        return $alerta->update([
            'resuelta' => true,
            'fecha_resolucion' => now(),
            'observaciones' => $observaciones,
        ])->resuelta === true;
    }

    public function getPendientes(): array
    {
        return AlertaTablero::pendientes()
                           ->activas()
                           ->with(['producto', 'grupo', 'semillero'])
                           ->orderBy('prioridad', 'desc')
                           ->orderBy('fecha_alerta', 'asc')
                           ->get()
                           ->toArray();
    }

    public function getVencidas(): array
    {
        return AlertaTablero::vencidas()
                           ->activas()
                           ->with(['producto', 'grupo', 'semillero'])
                           ->orderBy('fecha_alerta', 'asc')
                           ->get()
                           ->toArray();
    }

    public function getProximasAVencer(int $dias = 7): array
    {
        return AlertaTablero::proximasAVencer($dias)
                           ->activas()
                           ->with(['producto', 'grupo', 'semillero'])
                           ->orderBy('fecha_alerta', 'asc')
                           ->get()
                           ->toArray();
    }

    public function getCriticas(): array
    {
        return AlertaTablero::criticas()
                           ->pendientes()
                           ->with(['producto', 'grupo', 'semillero'])
                           ->orderBy('fecha_alerta', 'asc')
                           ->get()
                           ->toArray();
    }

    public function getByCorte(int $corteId): array
    {
        return AlertaTablero::porCorte($corteId)
                           ->activas()
                           ->with(['producto', 'grupo', 'semillero'])
                           ->orderBy('prioridad', 'desc')
                           ->get()
                           ->toArray();
    }

    public function getByGrupo(int $grupoId): array
    {
        return AlertaTablero::porGrupo($grupoId)
                           ->pendientes()
                           ->with('producto')
                           ->orderBy('prioridad', 'desc')
                           ->get()
                           ->toArray();
    }

    public function getBySemillero(int $semilleroId): array
    {
        return AlertaTablero::porSemillero($semilleroId)
                           ->pendientes()
                           ->with('producto')
                           ->orderBy('prioridad', 'desc')
                           ->get()
                           ->toArray();
    }
}
