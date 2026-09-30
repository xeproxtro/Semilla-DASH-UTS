<?php

namespace App\Services;

use App\Repositories\ActividadRepository;
use App\Repositories\ProyectoRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class ActividadService
{
    protected ActividadRepository $actividadRepository;
    protected ProyectoRepository $proyectoRepository;
    protected UserRepository $userRepository;

    public function __construct(
        ActividadRepository $actividadRepository,
        ProyectoRepository $proyectoRepository,
        UserRepository $userRepository
    ) {
        $this->actividadRepository = $actividadRepository;
        $this->proyectoRepository = $proyectoRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllActividades(array $filters = [])
    {
        return $this->actividadRepository->all($filters);
    }

    public function getActividadById(int $id)
    {
        return $this->actividadRepository->findById($id);
    }

    public function createActividad(array $data)
    {
        try {
            $this->validateActividadData($data);
            
            if (empty($data['proyecto_id'])) {
                throw new Exception("El proyecto es obligatorio");
            }

            $proyecto = $this->proyectoRepository->findById($data['proyecto_id']);
            if (!$proyecto) {
                throw new Exception("El proyecto especificado no existe");
            }

            if (isset($data['responsable_id'])) {
                $userRepo = app(UserRepository::class);
                $responsable = $userRepo->findById($data['responsable_id']);
                if (!$responsable) {
                    throw new Exception("El responsable especificado no existe");
                }
            }

            $actividad = $this->actividadRepository->create($data);
            
            Log::info("Actividad creada: {$actividad->nombre}", ['actividad_id' => $actividad->id]);
            return $actividad;
        } catch (Exception $e) {
            Log::error("Error al crear actividad: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateActividad(int $id, array $data)
    {
        try {
            $this->validateActividadData($data, true);
            
            if (isset($data['proyecto_id'])) {
                $proyecto = $this->proyectoRepository->findById($data['proyecto_id']);
                if (!$proyecto) {
                    throw new Exception("El proyecto especificado no existe");
                }
            }

            if (isset($data['responsable_id'])) {
                $userRepo = app(UserRepository::class);
                $responsable = $userRepo->findById($data['responsable_id']);
                if (!$responsable) {
                    throw new Exception("El responsable especificado no existe");
                }
            }

            $actividad = $this->actividadRepository->update($id, $data);
            
            Log::info("Actividad actualizada: {$actividad->nombre}", ['actividad_id' => $id]);
            return $actividad;
        } catch (Exception $e) {
            Log::error("Error al actualizar actividad: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteActividad(int $id)
    {
        try {
            $actividad = $this->actividadRepository->findById($id);
            
            if ($actividad && $actividad->subactividades()->count() > 0) {
                throw new Exception("No se puede eliminar la actividad porque tiene subactividades");
            }

            $result = $this->actividadRepository->delete($id);
            
            Log::info("Actividad eliminada", ['actividad_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar actividad: " . $e->getMessage());
            throw $e;
        }
    }

    public function cambiarEstadoActividad(int $id, string $nuevoEstado): bool
    {
        try {
            $estadosValidos = ['Pendiente', 'En Progreso', 'Completado', 'Atrasado', 'Cancelado'];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                throw new Exception("Estado no válido: {$nuevoEstado}");
            }

            return $this->actividadRepository->update($id, ['estado' => $nuevoEstado])->estado === $nuevoEstado;
        } catch (Exception $e) {
            Log::error("Error al cambiar estado de actividad: " . $e->getMessage());
            throw $e;
        }
    }

    public function getActividadesPorProyecto(int $proyectoId): array
    {
        return $this->actividadRepository->getByProyecto($proyectoId);
    }

    public function getArbolActividadesPorProyecto(int $proyectoId): array
    {
        return $this->actividadRepository->getArbolByProyecto($proyectoId);
    }

    public function getActividadesAtrasadas(int $proyectoId): array
    {
        return $this->actividadRepository->getAtrasadas($proyectoId);
    }

    public function getActividadesPorResponsable(int $responsableId): array
    {
        return $this->actividadRepository->getPorResponsable($responsableId);
    }

    protected function validateActividadData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['nombre'])) {
                throw new Exception("El nombre de la actividad es obligatorio");
            }
            if (empty($data['tipo'])) {
                throw new Exception("El tipo de actividad es obligatorio");
            }
            if (empty($data['fecha_inicio'])) {
                throw new Exception("La fecha de inicio es obligatoria");
            }
        }

        if (isset($data['tipo'])) {
            $tiposValidos = ['Actividad', 'Hito', 'Entregable', 'Tarea'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new Exception("Tipo de actividad no válido");
            }
        }

        if (isset($data['fecha_inicio']) && isset($data['fecha_fin'])) {
            if (strtotime($data['fecha_fin']) < strtotime($data['fecha_inicio'])) {
                throw new Exception("La fecha fin no puede ser anterior a la fecha inicio");
            }
        }

        if (isset($data['porcentaje_avance'])) {
            if ($data['porcentaje_avance'] < 0 || $data['porcentaje_avance'] > 100) {
                throw new Exception("El porcentaje de avance debe estar entre 0 y 100");
            }
        }
    }
}
