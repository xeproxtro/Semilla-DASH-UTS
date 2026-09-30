<?php

namespace App\Services;

use App\Repositories\ProyectoRepository;
use App\Repositories\ParticipanteProyectoRepository;
use App\Repositories\GrupoRepository;
use App\Repositories\SemilleroRepository;
use App\Repositories\InstitucionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class ProyectoService
{
    protected ProyectoRepository $proyectoRepository;
    protected ParticipanteProyectoRepository $participanteRepository;
    protected GrupoRepository $grupoRepository;
    protected SemilleroRepository $semilleroRepository;
    protected InstitucionRepository $institucionRepository;
    protected UserRepository $userRepository;

    public function __construct(
        ProyectoRepository $proyectoRepository,
        ParticipanteProyectoRepository $participanteRepository,
        GrupoRepository $grupoRepository,
        SemilleroRepository $semilleroRepository,
        InstitucionRepository $institucionRepository,
        UserRepository $userRepository
    ) {
        $this->proyectoRepository = $proyectoRepository;
        $this->participanteRepository = $participanteRepository;
        $this->grupoRepository = $grupoRepository;
        $this->semilleroRepository = $semilleroRepository;
        $this->institucionRepository = $institucionRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllProyectos(array $filters = [])
    {
        return $this->proyectoRepository->all($filters);
    }

    public function getProyectoById(int $id)
    {
        return $this->proyectoRepository->findById($id);
    }

    public function createProyecto(array $data)
    {
        try {
            $this->validateProyectoData($data);
            
            if (empty($data['director_id'])) {
                throw new Exception("El director del proyecto es obligatorio");
            }

            $userRepo = app(UserRepository::class);
            $director = $userRepo->findById($data['director_id']);
            if (!$director) {
                throw new Exception("El director especificado no existe");
            }

            $proyecto = $this->proyectoRepository->create($data);
            
            if (isset($data['grupos']) && is_array($data['grupos'])) {
                $this->proyectoRepository->syncGrupos($proyecto->id, $data['grupos']);
            }

            if (isset($data['semilleros']) && is_array($data['semilleros'])) {
                $this->proyectoRepository->syncSemilleros($proyecto->id, $data['semilleros']);
            }

            if (isset($data['participantes']) && is_array($data['participantes'])) {
                $this->participanteRepository->syncParticipantes($proyecto->id, $data['participantes']);
            }

            Log::info("Proyecto creado: {$proyecto->titulo}", ['proyecto_id' => $proyecto->id]);
            return $proyecto;
        } catch (Exception $e) {
            Log::error("Error al crear proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateProyecto(int $id, array $data)
    {
        try {
            $this->validateProyectoData($data, true);
            
            if (isset($data['director_id'])) {
                $userRepo = app(UserRepository::class);
            $director = $userRepo->findById($data['director_id']);
                if (!$director) {
                    throw new Exception("El director especificado no existe");
                }
            }

            $proyecto = $this->proyectoRepository->update($id, $data);
            
            if (isset($data['grupos']) && is_array($data['grupos'])) {
                $this->proyectoRepository->syncGrupos($id, $data['grupos']);
            }

            if (isset($data['semilleros']) && is_array($data['semilleros'])) {
                $this->proyectoRepository->syncSemilleros($id, $data['semilleros']);
            }

            if (isset($data['participantes']) && is_array($data['participantes'])) {
                $this->participanteRepository->syncParticipantes($id, $data['participantes']);
            }

            Log::info("Proyecto actualizado: {$proyecto->titulo}", ['proyecto_id' => $id]);
            return $proyecto;
        } catch (Exception $e) {
            Log::error("Error al actualizar proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteProyecto(int $id)
    {
        try {
            $proyecto = $this->proyectoRepository->findById($id);
            
            if ($proyecto && $proyecto->participantesActivos()->count() > 0) {
                throw new Exception("No se puede eliminar el proyecto porque tiene participantes activos");
            }

            if ($proyecto && $proyecto->actividadesActivas()->count() > 0) {
                throw new Exception("No se puede eliminar el proyecto porque tiene actividades activas");
            }

            $result = $this->proyectoRepository->delete($id);
            
            Log::info("Proyecto eliminado", ['proyecto_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function addGrupoToProyecto(int $proyectoId, int $grupoId, string $rol = 'Principal', ?string $observaciones = null)
    {
        try {
            $grupo = $this->grupoRepository->findById($grupoId);
            if (!$grupo) {
                throw new Exception("Grupo no encontrado");
            }

            $this->proyectoRepository->addGrupo($proyectoId, $grupoId, $rol, $observaciones);
            
            Log::info("Grupo agregado al proyecto", ['proyecto_id' => $proyectoId, 'grupo_id' => $grupoId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar grupo al proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeGrupoFromProyecto(int $proyectoId, int $grupoId)
    {
        try {
            $this->proyectoRepository->removeGrupo($proyectoId, $grupoId);
            
            Log::info("Grupo removido del proyecto", ['proyecto_id' => $proyectoId, 'grupo_id' => $grupoId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover grupo del proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function addSemilleroToProyecto(int $proyectoId, int $semilleroId, string $rol = 'Principal', ?string $observaciones = null)
    {
        try {
            $semillero = $this->semilleroRepository->findById($semilleroId);
            if (!$semillero) {
                throw new Exception("Semillero no encontrado");
            }

            $this->proyectoRepository->addSemillero($proyectoId, $semilleroId, $rol, $observaciones);
            
            Log::info("Semillero agregado al proyecto", ['proyecto_id' => $proyectoId, 'semillero_id' => $semilleroId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar semillero al proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeSemilleroFromProyecto(int $proyectoId, int $semilleroId)
    {
        try {
            $this->proyectoRepository->removeSemillero($proyectoId, $semilleroId);
            
            Log::info("Semillero removido del proyecto", ['proyecto_id' => $proyectoId, 'semillero_id' => $semilleroId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover semillero del proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function addInstitucionToProyecto(int $proyectoId, int $institucionId, string $tipoParticipacion = 'Ejecutora', ?string $observaciones = null)
    {
        try {
            $institucion = $this->institucionRepository->findById($institucionId);
            if (!$institucion) {
                throw new Exception("Institución no encontrada");
            }

            $this->proyectoRepository->addInstitucion($proyectoId, $institucionId, $tipoParticipacion, $observaciones);
            
            Log::info("Institución agregada al proyecto", ['proyecto_id' => $proyectoId, 'institucion_id' => $institucionId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar institución al proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeInstitucionFromProyecto(int $proyectoId, int $institucionId)
    {
        try {
            $this->proyectoRepository->removeInstitucion($proyectoId, $institucionId);
            
            Log::info("Institución removida del proyecto", ['proyecto_id' => $proyectoId, 'institucion_id' => $institucionId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover institución del proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function cambiarEstadoProyecto(int $id, string $nuevoEstado): bool
    {
        try {
            $estadosValidos = ['Propuesto', 'Aprobado', 'En Ejecucion', 'Suspendido', 'Finalizado', 'Cancelado'];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                throw new Exception("Estado no válido: {$nuevoEstado}");
            }

            return $this->proyectoRepository->update($id, ['estado' => $nuevoEstado])->estado === $nuevoEstado;
        } catch (Exception $e) {
            Log::error("Error al cambiar estado del proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function deactivateProyecto(int $id): bool
    {
        try {
            return $this->proyectoRepository->update($id, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateProyecto(int $id): bool
    {
        try {
            return $this->proyectoRepository->update($id, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar proyecto: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProyectosPorEstado(string $estado): array
    {
        return $this->proyectoRepository->getByEstado($estado);
    }

    public function getProyectosPorDirector(int $directorId): array
    {
        return $this->proyectoRepository->getByDirector($directorId);
    }

    public function getAllActiveProyectos(): array
    {
        return $this->proyectoRepository->getAllActive();
    }

    protected function validateProyectoData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['codigo'])) {
                throw new Exception("El código del proyecto es obligatorio");
            }
            if (empty($data['titulo'])) {
                throw new Exception("El título del proyecto es obligatorio");
            }
            if (empty($data['objetivo_general'])) {
                throw new Exception("El objetivo general es obligatorio");
            }
            if (empty($data['tipo'])) {
                throw new Exception("El tipo de proyecto es obligatorio");
            }
            if (empty($data['fecha_inicio'])) {
                throw new Exception("La fecha de inicio es obligatoria");
            }
        }

        if (isset($data['codigo'])) {
            $codigo = strtoupper(trim($data['codigo']));
            if (!preg_match('/^[A-Z0-9-]+$/', $codigo)) {
                throw new Exception("El código solo puede contener letras, números y guiones");
            }
            $data['codigo'] = $codigo;
        }

        if (isset($data['tipo'])) {
            $tiposValidos = ['investigacion_desarrollo', 'investigacion_creacion', 'idi', 'extension', 'formativo'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new Exception("Tipo de proyecto no válido");
            }
        }

        if (isset($data['fecha_inicio']) && isset($data['fecha_fin'])) {
            if (strtotime($data['fecha_fin']) < strtotime($data['fecha_inicio'])) {
                throw new Exception("La fecha fin no puede ser anterior a la fecha inicio");
            }
        }
    }
}
