<?php

namespace App\Repositories;

use App\Models\RevisionExpediente;
use Illuminate\Pagination\LengthAwarePaginator;

class RevisionExpedienteRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = RevisionExpediente::with(['producto', 'listaChequeo', 'respuestasActivas.itemLista']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['estado'])) {
            $query->porEstado($filters['estado']);
        }

        if (isset($filters['producto_id'])) {
            $query->porProducto($filters['producto_id']);
        }

        if (isset($filters['lista_id'])) {
            $query->porLista($filters['lista_id']);
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?RevisionExpediente
    {
        return RevisionExpediente::with([
            'producto',
            'listaChequeo',
            'listaChequeo.itemsActivos',
            'respuestasActivas.itemLista',
            'respuestasActivas.respondidoPor'
        ])->find($id);
    }

    public function create(array $data): RevisionExpediente
    {
        return RevisionExpediente::create($data);
    }

    public function update(int $id, array $data): RevisionExpediente
    {
        $revision = RevisionExpediente::findOrFail($id);
        $revision->update($data);
        return $revision->fresh();
    }

    public function delete(int $id): bool
    {
        return RevisionExpediente::findOrFail($id)->delete();
    }

    public function getByProducto(int $productoId): array
    {
        return RevisionExpediente::porProducto($productoId)
                                ->activas()
                                ->with('listaChequeo')
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->toArray();
    }

    public function getPendientes(): array
    {
        return RevisionExpediente::pendientes()
                                ->activas()
                                ->with('producto')
                                ->get()
                                ->toArray();
    }

    public function getCompletados(): array
    {
        return RevisionExpediente::completados()
                                ->activas()
                                ->with('producto')
                                ->get()
                                ->toArray();
    }

    public function cambiarEstado(int $id, string $nuevoEstado, ?string $observaciones = null): bool
    {
        $revision = RevisionExpediente::findOrFail($id);
        return $revision->update([
            'estado' => $nuevoEstado,
            'fecha_estado' => now(),
            'observaciones' => $observaciones,
        ])->estado === $nuevoEstado;
    }

    public function registrarRespuesta(int $revisionId, int $itemId, array $datos): bool
    {
        $revision = RevisionExpediente::findOrFail($revisionId);
        
        $revision->respuestas()->updateOrCreate(
            ['item_lista_id' => $itemId],
            [
                'respuesta' => $datos['respuesta'] ?? null,
                'cumple' => $datos['cumple'] ?? false,
                'observaciones' => $datos['observaciones'] ?? null,
                'respondido_por' => $datos['respondido_por'] ?? null,
                'fecha_respuesta' => now(),
                'activo' => true,
            ]
        );

        $revision->calcularCompletitud();
        return true;
    }

    public function calcularCompletitud(int $id): int
    {
        $revision = RevisionExpediente::findOrFail($id);
        $revision->calcularCompletitud();
        return $revision->porcentaje_completitud;
    }
}
