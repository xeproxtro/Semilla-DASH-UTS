<?php

namespace App\Repositories;

use App\Models\ParticipanteProyecto;
use Illuminate\Pagination\LengthAwarePaginator;

class ParticipanteProyectoRepository
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = ParticipanteProyecto::with(['proyecto', 'user']);

        if (isset($filters['activo'])) {
            $query->where('activo', $filters['activo']);
        }

        if (isset($filters['rol'])) {
            $query->porRol($filters['rol']);
        }

        if (isset($filters['user_id'])) {
            $query->porUsuario($filters['user_id']);
        }

        if (isset($filters['proyecto_id'])) {
            $query->porProyecto($filters['proyecto_id']);
        }

        return $query->orderBy('fecha_inicio', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?ParticipanteProyecto
    {
        return ParticipanteProyecto::with(['proyecto', 'user'])->find($id);
    }

    public function create(array $data): ParticipanteProyecto
    {
        return ParticipanteProyecto::create($data);
    }

    public function update(int $id, array $data): ParticipanteProyecto
    {
        $participante = ParticipanteProyecto::findOrFail($id);
        $participante->update($data);
        return $participante->fresh();
    }

    public function delete(int $id): bool
    {
        return ParticipanteProyecto::findOrFail($id)->delete();
    }

    public function getByProyecto(int $proyectoId): array
    {
        return ParticipanteProyecto::porProyecto($proyectoId)
                                  ->activos()
                                  ->with('user')
                                  ->get()
                                  ->toArray();
    }

    public function getByUsuario(int $userId): array
    {
        return ParticipanteProyecto::porUsuario($userId)
                                  ->activos()
                                  ->with('proyecto')
                                  ->get()
                                  ->toArray();
    }

    public function syncParticipantes(int $proyectoId, array $participantesData): void
    {
        $syncData = [];

        foreach ($participantesData as $participanteData) {
            $syncData[$participanteData['user_id']] = [
                'rol' => $participanteData['rol'] ?? 'Investigador',
                'horas_dedicacion' => $participanteData['horas_dedicacion'] ?? 0,
                'presupuesto_asignado' => $participanteData['presupuesto_asignado'] ?? 0,
                'fecha_inicio' => $participanteData['fecha_inicio'] ?? now(),
                'fecha_fin' => $participanteData['fecha_fin'] ?? null,
                'activo' => true,
                'observaciones' => $participanteData['observaciones'] ?? null,
            ];
        }

        ParticipanteProyecto::where('proyecto_id', $proyectoId)->delete();
        
        foreach ($syncData as $userId => $data) {
            ParticipanteProyecto::create([
                'proyecto_id' => $proyectoId,
                'user_id' => $userId,
                ...$data
            ]);
        }
    }
}
