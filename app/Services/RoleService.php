<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class RoleService
{
    protected RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAllRoles(array $filters = [])
    {
        return $this->roleRepository->all($filters);
    }

    public function getRoleById(int $id)
    {
        return $this->roleRepository->findById($id);
    }

    public function getRoleBySlug(string $slug)
    {
        return $this->roleRepository->findBySlug($slug);
    }

    public function createRole(array $data)
    {
        try {
            $this->validateRoleData($data);
            
            $role = $this->roleRepository->create($data);
            
            Log::info("Rol creado: {$role->nombre}", ['role_id' => $role->id]);
            return $role;
        } catch (Exception $e) {
            Log::error("Error al crear rol: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRole(int $id, array $data)
    {
        try {
            $this->validateRoleData($data, true);
            
            $role = $this->roleRepository->update($id, $data);
            
            Log::info("Rol actualizado: {$role->nombre}", ['role_id' => $id]);
            return $role;
        } catch (Exception $e) {
            Log::error("Error al actualizar rol: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteRole(int $id)
    {
        try {
            $role = $this->roleRepository->findById($id);
            
            if ($role && $role->users()->count() > 0) {
                throw new Exception("No se puede eliminar el rol porque tiene usuarios asignados");
            }

            $result = $this->roleRepository->delete($id);
            
            Log::info("Rol eliminado", ['role_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar rol: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllActiveRoles(): array
    {
        return $this->roleRepository->getAllActive();
    }

    public function initializeDefaultRoles(): void
    {
        $defaultRoles = [
            [
                'nombre' => 'Integrante de Semillero',
                'slug' => 'integrante_semillero',
                'descripcion' => 'Registra participación y evidencias propias en semilleros de investigación',
                'activo' => true,
            ],
            [
                'nombre' => 'Coordinador de Semillero',
                'slug' => 'coordinador_semillero',
                'descripcion' => 'Gestiona el plan formativo y a los integrantes del semillero',
                'activo' => true,
            ],
            [
                'nombre' => 'Investigador',
                'slug' => 'investigador',
                'descripcion' => 'Mantiene referencias de sus productos de investigación',
                'activo' => true,
            ],
            [
                'nombre' => 'Líder de Grupo',
                'slug' => 'lider_grupo',
                'descripcion' => 'Administra el plan estratégico del grupo, líneas de investigación e inventario de producción',
                'activo' => true,
            ],
            [
                'nombre' => 'Director de Proyecto',
                'slug' => 'director_proyecto',
                'descripcion' => 'Gestiona cronograma y presupuesto de su proyecto de investigación',
                'activo' => true,
            ],
            [
                'nombre' => 'Gestor de Investigación',
                'slug' => 'gestor_investigacion',
                'descripcion' => 'Revisa evidencias y normaliza metadatos de productos',
                'activo' => true,
            ],
            [
                'nombre' => 'Aval Institucional',
                'slug' => 'aval_institucional',
                'descripcion' => 'Emite decisiones formales sobre los expedientes de investigación',
                'activo' => true,
            ],
            [
                'nombre' => 'Administrador',
                'slug' => 'administrador',
                'descripcion' => 'Gestiona catálogos, ventanas de observación, auditoría y seguridad del sistema',
                'activo' => true,
            ],
        ];

        foreach ($defaultRoles as $roleData) {
            $existingRole = $this->roleRepository->findBySlug($roleData['slug']);
            
            if (!$existingRole) {
                $this->roleRepository->create($roleData);
                Log::info("Rol por defecto creado: {$roleData['nombre']}");
            }
        }
    }

    protected function validateRoleData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['nombre'])) {
                throw new Exception("El nombre del rol es obligatorio");
            }
            if (empty($data['slug'])) {
                throw new Exception("El slug del rol es obligatorio");
            }
        }

        if (isset($data['slug'])) {
            $slug = strtolower($data['slug']);
            $slug = preg_replace('/[^a-z0-9_]/', '_', $slug);
            $data['slug'] = $slug;
        }
    }
}
