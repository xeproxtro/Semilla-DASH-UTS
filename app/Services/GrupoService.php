<?php

namespace App\Services;

use App\Repositories\GrupoRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class GrupoService
{
    protected GrupoRepository $grupoRepository;
    protected UserRepository $userRepository;

    public function __construct(GrupoRepository $grupoRepository, UserRepository $userRepository)
    {
        $this->grupoRepository = $grupoRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllGrupos(array $filters = [])
    {
        return $this->grupoRepository->all($filters);
    }

    public function getGrupoById(int $id)
    {
        return $this->grupoRepository->findById($id);
    }

    public function createGrupo(array $data)
    {
        try {
            $this->validateGrupoData($data);
            
            if (empty($data['lider_id'])) {
                throw new Exception("El líder del grupo es obligatorio");
            }

            $lider = $this->userRepository->findById($data['lider_id']);
            if (!$lider) {
                throw new Exception("El líder especificado no existe");
            }

            $grupo = $this->grupoRepository->create($data);
            
            if (isset($data['miembros']) && is_array($data['miembros'])) {
                foreach ($data['miembros'] as $miembro) {
                    $this->grupoRepository->addMember(
                        $grupo->id,
                        $miembro['user_id'],
                        $miembro['rol'] ?? 'Investigador',
                        $miembro['observaciones'] ?? null
                    );
                }
            }

            Log::info("Grupo creado: {$grupo->nombre}", ['grupo_id' => $grupo->id]);
            return $grupo;
        } catch (Exception $e) {
            Log::error("Error al crear grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateGrupo(int $id, array $data)
    {
        try {
            $this->validateGrupoData($data, true);
            
            if (isset($data['lider_id'])) {
                $lider = $this->userRepository->findById($data['lider_id']);
                if (!$lider) {
                    throw new Exception("El líder especificado no existe");
                }
            }

            $grupo = $this->grupoRepository->update($id, $data);
            
            if (isset($data['miembros']) && is_array($data['miembros'])) {
                $this->grupoRepository->syncMembers($id, $data['miembros']);
            }

            Log::info("Grupo actualizado: {$grupo->nombre}", ['grupo_id' => $id]);
            return $grupo;
        } catch (Exception $e) {
            Log::error("Error al actualizar grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteGrupo(int $id)
    {
        try {
            $grupo = $this->grupoRepository->findById($id);
            
            if ($grupo && $grupo->miembrosActivos()->count() > 0) {
                throw new Exception("No se puede eliminar el grupo porque tiene miembros activos");
            }

            if ($grupo && $grupo->semillerosActivos()->count() > 0) {
                throw new Exception("No se puede eliminar el grupo porque tiene semilleros articulados");
            }

            $result = $this->grupoRepository->delete($id);
            
            Log::info("Grupo eliminado", ['grupo_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function addMemberToGrupo(int $grupoId, int $userId, string $rol = 'Investigador', ?string $observaciones = null)
    {
        try {
            $grupo = $this->grupoRepository->findById($grupoId);
            if (!$grupo) {
                throw new Exception("Grupo no encontrado");
            }

            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }

            $this->grupoRepository->addMember($grupoId, $userId, $rol, $observaciones);
            
            Log::info("Miembro agregado al grupo", ['grupo_id' => $grupoId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar miembro al grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeMemberFromGrupo(int $grupoId, int $userId)
    {
        try {
            $this->grupoRepository->removeMember($grupoId, $userId);
            
            Log::info("Miembro removido del grupo", ['grupo_id' => $grupoId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover miembro del grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function deactivateGrupo(int $id): bool
    {
        try {
            return $this->grupoRepository->update($id, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateGrupo(int $id): bool
    {
        try {
            return $this->grupoRepository->update($id, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar grupo: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllActiveGrupos(): array
    {
        return $this->grupoRepository->getAllActive();
    }

    protected function validateGrupoData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['codigo'])) {
                throw new Exception("El código del grupo es obligatorio");
            }
            if (empty($data['nombre'])) {
                throw new Exception("El nombre del grupo es obligatorio");
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
