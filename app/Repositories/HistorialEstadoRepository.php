<?php

namespace App\Repositories;

use App\Models\HistorialEstado;
use Illuminate\Pagination\LengthAwarePaginator;

class HistorialEstadoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = HistorialEstado::with(['producto', 'realizadoPor']);

        if (isset($filters['producto_id'])) {
            $query->porProducto($filters['producto_id']);
        }

        if (isset($filters['user_id'])) {
            $query->porUsuario($filters['user_id']);
        }

        if (isset($filters['accion'])) {
            $query->porAccion($filters['accion']);
        }

        if (isset($filters['fecha_inicio'])) {
            $query->porPeriodo($filters['fecha_inicio'], $filters['fecha_fin'] ?? null);
        }

        return $query->orderBy('fecha_accion', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): HistorialEstado
    {
        return HistorialEstado::create($data);
    }

    public function getByProducto(int $productoId): array
    {
        return HistorialEstado::porProducto($productoId)
                                ->with('realizadoPor')
                                ->orderBy('fecha_accion', 'desc')
                                ->get()
                                ->toArray();
    }

    public function getByUsuario(int $userId): array
    {
        return HistorialEstado::porUsuario($userId)
                                ->with('producto')
                                ->orderBy('fecha_accion', 'desc')
                                ->get()
                                ->toArray();
    }

    public function getRecientes(int $dias = 30): array
    {
        return HistorialEstado::recientes($dias)
                                ->with(['producto', 'realizadoPor'])
                                ->orderBy('fecha_accion', 'desc')
                                ->get()
                                ->toArray();
    }

    public function registrarTransicion(
        int $productoId,
        int $userId,
        string $accion,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $observaciones = null,
        ?string $rolUsuario = null,
        ?string $direccionIp = null,
        ?array $metadata = null
    ): HistorialEstado {
        return HistorialEstado::create([
            'producto_id' => $productoId,
            'realizado_por' => $userId,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'observaciones' => $observaciones,
            'rol_usuario' => $rolUsuario,
            'direccion_ip' => $direccionIp,
            'metadata' => $metadata,
        ]);
    }
}
