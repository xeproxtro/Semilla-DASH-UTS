<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RoleController
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $roles = $this->roleService->getAllRoles($filters);

            return response()->json([
                'success' => true,
                'data' => $roles->items(),
                'pagination' => [
                    'total' => $roles->total(),
                    'per_page' => $roles->perPage(),
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->getRoleById($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $role,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:roles,nombre',
                'slug' => 'required|string|max:50|unique:roles,slug',
                'descripcion' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $role = $this->roleService->createRole($validated);

            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => $role,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:50|unique:roles,nombre,' . $id,
                'slug' => 'sometimes|required|string|max:50|unique:roles,slug,' . $id,
                'descripcion' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $role = $this->roleService->updateRole($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'data' => $role,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->roleService->deleteRole($id);

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllActive(): JsonResponse
    {
        try {
            $roles = $this->roleService->getAllActiveRoles();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles activos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function initializeDefaultRoles(): JsonResponse
    {
        try {
            $this->roleService->initializeDefaultRoles();

            return response()->json([
                'success' => true,
                'message' => 'Roles por defecto inicializados exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al inicializar roles por defecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
