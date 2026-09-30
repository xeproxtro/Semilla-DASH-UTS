<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    protected UserRepository $userRepository;
    protected RoleRepository $roleRepository;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function getAllUsers(array $filters = [])
    {
        return $this->userRepository->all($filters);
    }

    public function getUserById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data)
    {
        try {
            $this->validateUserData($data);
            
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->create($data);
            
            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->userRepository->syncRoles($user->id, $data['roles']);
            }

            Log::info("Usuario creado: {$user->email}", ['user_id' => $user->id]);
            return $user;
        } catch (Exception $e) {
            Log::error("Error al crear usuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser(int $id, array $data)
    {
        try {
            $this->validateUserData($data, true);
            
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->update($id, $data);
            
            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->userRepository->syncRoles($user->id, $data['roles']);
            }

            Log::info("Usuario actualizado: {$user->email}", ['user_id' => $user->id]);
            return $user;
        } catch (Exception $e) {
            Log::error("Error al actualizar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteUser(int $id)
    {
        try {
            $user = $this->userRepository->findById($id);
            $result = $this->userRepository->delete($id);
            
            Log::info("Usuario eliminado: {$user->email}", ['user_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function assignRoleToUser(int $userId, string $roleSlug, ?string $fechaExpiracion = null)
    {
        try {
            $role = $this->roleRepository->findBySlug($roleSlug);
            
            if (!$role) {
                throw new Exception("Rol no encontrado: {$roleSlug}");
            }

            $this->userRepository->assignRole($userId, $role->id, $fechaExpiracion);
            
            Log::info("Rol asignado a usuario", ['user_id' => $userId, 'role' => $roleSlug]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al asignar rol: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeRoleFromUser(int $userId, string $roleSlug)
    {
        try {
            $role = $this->roleRepository->findBySlug($roleSlug);
            
            if (!$role) {
                throw new Exception("Rol no encontrado: {$roleSlug}");
            }

            $this->userRepository->removeRole($userId, $role->id);
            
            Log::info("Rol removido de usuario", ['user_id' => $userId, 'role' => $roleSlug]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover rol: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateLastLogin(int $userId): void
    {
        try {
            $this->userRepository->update($userId, ['ultimo_acceso' => now()]);
        } catch (Exception $e) {
            Log::error("Error al actualizar último acceso: " . $e->getMessage());
        }
    }

    public function deactivateUser(int $userId): bool
    {
        try {
            return $this->userRepository->update($userId, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateUser(int $userId): bool
    {
        try {
            return $this->userRepository->update($userId, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    protected function validateUserData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['email'])) {
                throw new Exception("El email es obligatorio");
            }
            if (empty($data['password'])) {
                throw new Exception("La contraseña es obligatoria");
            }
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del email es inválido");
        }

        if (isset($data['orcid_id']) && !$this->validateORCID($data['orcid_id'])) {
            throw new Exception("El formato del ORCID es inválido");
        }
    }

    protected function validateORCID(string $orcid): bool
    {
        return (bool) preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[0-9X]$/', $orcid);
    }
}
