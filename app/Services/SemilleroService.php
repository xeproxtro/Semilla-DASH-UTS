<?php

namespace App\Services;

use App\Repositories\SemilleroRepository;
use App\Repositories\UserRepository;
use App\Repositories\GrupoRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class SemilleroService
{
    protected SemilleroRepository $semilleroRepository;
    protected UserRepository $userRepository;
    protected GrupoRepository $grupoRepository;

    public function __construct(
        SemilleroRepository $semilleroRepository,
        UserRepository $userRepository,
        GrupoRepository $grupoRepository
    ) {
        $this->semilleroRepository = $semilleroRepository;
        $this->userRepository = $userRepository;
        $this->grupoRepository = $grupoRepository;
    }

    public function getAllSemilleros(array $filters = [])
    {
        return $this->semilleroRepository->all($filters);
    }

    public function getSemilleroById(int $id)
    {
        return $this->semilleroRepository->findById($id);
    }

    public function createSemillero(array $data)
    {
        try {
            $this->validateSemilleroData($data);
            
            if (empty($data['coordinador_id'])) {
                throw new Exception("El coordinador del semillero es obligatorio");
            }

            $coordinador = $this->userRepository->findById($data['coordinador_id']);
            if (!$coordinador) {
                throw new Exception("El coordinador especificado no existe");
            }

            $semillero = $this->semilleroRepository->create($data);
            
            if (isset($data['integrantes']) && is_array($data['integrantes'])) {
                foreach ($data['integrantes'] as $integrante) {
                    $this->semilleroRepository->addMember(
                        $semillero->id,
                        $integrante['user_id'],
                        $integrante['rol'] ?? 'Integrante',
                        $integrante['observaciones'] ?? null
                    );
                }
            }

            if (isset($data['grupos']) && is_array($data['grupos'])) {
                foreach ($data['grupos'] as $grupo) {
                    $this->semilleroRepository->articulateWithGrupo(
                        $semillero->id,
                        $grupo['grupo_id'],
                        $grupo['observaciones'] ?? null
                    );
                }
            }

            Log::info("Semillero creado: {$semillero->nombre}", ['semillero_id' => $semillero->id]);
            return $semillero;
        } catch (Exception $e) {
            Log::error("Error al crear semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateSemillero(int $id, array $data)
    {
        try {
            $this->validateSemilleroData($data, true);
            
            if (isset($data['coordinador_id'])) {
                $coordinador = $this->userRepository->findById($data['coordinador_id']);
                if (!$coordinador) {
                    throw new Exception("El coordinador especificado no existe");
                }
            }

            $semillero = $this->semilleroRepository->update($id, $data);
            
            if (isset($data['integrantes']) && is_array($data['integrantes'])) {
                $this->semilleroRepository->syncMembers($id, $data['integrantes']);
            }

            if (isset($data['grupos']) && is_array($data['grupos'])) {
                $this->semilleroRepository->syncGrupos($id, $data['grupos']);
            }

            Log::info("Semillero actualizado: {$semillero->nombre}", ['semillero_id' => $id]);
            return $semillero;
        } catch (Exception $e) {
            Log::error("Error al actualizar semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteSemillero(int $id)
    {
        try {
            $semillero = $this->semilleroRepository->findById($id);
            
            if ($semillero && $semillero->integrantesActivos()->count() > 0) {
                throw new Exception("No se puede eliminar el semillero porque tiene integrantes activos");
            }

            $result = $this->semilleroRepository->delete($id);
            
            Log::info("Semillero eliminado", ['semillero_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function addMemberToSemillero(int $semilleroId, int $userId, string $rol = 'Integrante', ?string $observaciones = null)
    {
        try {
            $semillero = $this->semilleroRepository->findById($semilleroId);
            if (!$semillero) {
                throw new Exception("Semillero no encontrado");
            }

            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }

            $this->semilleroRepository->addMember($semilleroId, $userId, $rol, $observaciones);
            
            Log::info("Integrante agregado al semillero", ['semillero_id' => $semilleroId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar integrante al semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeMemberFromSemillero(int $semilleroId, int $userId)
    {
        try {
            $this->semilleroRepository->removeMember($semilleroId, $userId);
            
            Log::info("Integrante removido del semillero", ['semillero_id' => $semilleroId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover integrante del semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function articulateSemilleroWithGrupo(int $semilleroId, int $grupoId, ?string $observaciones = null)
    {
        try {
            $semillero = $this->semilleroRepository->findById($semilleroId);
            if (!$semillero) {
                throw new Exception("Semillero no encontrado");
            }

            $grupo = $this->grupoRepository->findById($grupoId);
            if (!$grupo) {
                throw new Exception("Grupo no encontrado");
            }

            $this->semilleroRepository->articulateWithGrupo($semilleroId, $grupoId, $observaciones);
            
            Log::info("Semillero articulado con grupo", ['semillero_id' => $semilleroId, 'grupo_id' => $grupoId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al articular semillero con grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeGrupoArticulation(int $semilleroId, int $grupoId)
    {
        try {
            $this->semilleroRepository->removeGrupoArticulation($semilleroId, $grupoId);
            
            Log::info("Articulación de semillero con grupo removida", ['semillero_id' => $semilleroId, 'grupo_id' => $grupoId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover articulación: " . $e->getMessage());
            throw $e;
        }
    }

    public function deactivateSemillero(int $id): bool
    {
        try {
            return $this->semilleroRepository->update($id, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateSemillero(int $id): bool
    {
        try {
            return $this->semilleroRepository->update($id, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar semillero: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllActiveSemilleros(): array
    {
        return $this->semilleroRepository->getAllActive();
    }

    protected function validateSemilleroData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['codigo'])) {
                throw new Exception("El código del semillero es obligatorio");
            }
            if (empty($data['nombre'])) {
                throw new Exception("El nombre del semillero es obligatorio");
            }
            if (empty($data['enfoque'])) {
                throw new Exception("El enfoque del semillero es obligatorio");
            }
        }

        if (isset($data['codigo'])) {
            $codigo = strtoupper(trim($data['codigo']));
            if (!preg_match('/^[A-Z0-9-]+$/', $codigo)) {
                throw new Exception("El código solo puede contener letras, números y guiones");
            }
            $data['codigo'] = $codigo;
        }

        if (isset($data['email_contacto']) && !filter_var($data['email_contacto'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del email de contacto es inválido");
        }
    }
}
