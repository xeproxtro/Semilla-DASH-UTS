<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController
{
    protected UserService $userService;
    protected RoleService $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'rol' => $request->get('rol'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $users = $this->userService->getAllUsers($filters);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'tipo_documento' => 'required|string|max:20',
                'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
                'password' => 'required|string|min:8',
                'orcid_id' => 'nullable|string|max:50|unique:users,orcid_id',
                'cvlac_id' => 'nullable|string|max:50|unique:users,cvlac_id',
                'activo' => 'boolean',
                'roles' => 'array',
                'roles.*' => 'exists:roles,id',
            ]);

            $user = $this->userService->createUser($validated);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user,
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
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombres' => 'sometimes|required|string|max:100',
                'apellidos' => 'sometimes|required|string|max:100',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'tipo_documento' => 'sometimes|required|string|max:20',
                'numero_documento' => 'sometimes|required|string|max:20|unique:users,numero_documento,' . $id,
                'password' => 'sometimes|required|string|min:8',
                'orcid_id' => 'nullable|string|max:50|unique:users,orcid_id,' . $id,
                'cvlac_id' => 'nullable|string|max:50|unique:users,cvlac_id,' . $id,
                'activo' => 'boolean',
                'roles' => 'array',
                'roles.*' => 'exists:roles,id',
            ]);

            $user = $this->userService->updateUser($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user,
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
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->userService->deleteUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function assignRole(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_slug' => 'required|string|exists:roles,slug',
                'fecha_expiracion' => 'nullable|date',
            ]);

            $this->userService->assignRoleToUser($id, $validated['role_slug'], $validated['fecha_expiracion']);

            return response()->json([
                'success' => true,
                'message' => 'Rol asignado exitosamente',
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
                'message' => 'Error al asignar rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeRole(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_slug' => 'required|string|exists:roles,slug',
            ]);

            $this->userService->removeRoleFromUser($id, $validated['role_slug']);

            return response()->json([
                'success' => true,
                'message' => 'Rol removido exitosamente',
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
                'message' => 'Error al remover rol',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->userService->activateUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->userService->deactivateUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
