<?php

namespace App\Http\Controllers;

use App\Services\GrupoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GrupoController
{
    protected GrupoService $grupoService;

    public function __construct(GrupoService $grupoService)
    {
        $this->grupoService = $grupoService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'categoria' => $request->get('categoria'),
                'lider_id' => $request->get('lider_id'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $grupos = $this->grupoService->getAllGrupos($filters);

            return response()->json([
                'success' => true,
                'data' => $grupos->items(),
                'pagination' => [
                    'total' => $grupos->total(),
                    'per_page' => $grupos->perPage(),
                    'current_page' => $grupos->currentPage(),
                    'last_page' => $grupos->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grupos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $grupo = $this->grupoService->getGrupoById($id);

            if (!$grupo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $grupo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:20|unique:grupos,codigo',
                'nombre' => 'required|string|max:200',
                'lider_id' => 'required|exists:users,id',
                'gruplac_id' => 'nullable|string|max:50|unique:grupos,gruplac_id',
                'mision' => 'nullable|string',
                'vision' => 'nullable|string',
                'plan_estrategico' => 'nullable|string',
                'categoria' => 'nullable|string|max:50',
                'fecha_constitucion' => 'nullable|date',
                'email_contacto' => 'nullable|email|max:100',
                'activo' => 'boolean',
                'miembros' => 'array',
                'miembros.*.user_id' => 'required|exists:users,id',
                'miembros.*.rol' => 'required|string|max:50',
                'miembros.*.observaciones' => 'nullable|string',
            ]);

            $grupo = $this->grupoService->createGrupo($validated);

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado exitosamente',
                'data' => $grupo,
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
                'message' => 'Error al crear grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'sometimes|required|string|max:20|unique:grupos,codigo,' . $id,
                'nombre' => 'sometimes|required|string|max:200',
                'lider_id' => 'sometimes|required|exists:users,id',
                'gruplac_id' => 'nullable|string|max:50|unique:grupos,gruplac_id,' . $id,
                'mision' => 'nullable|string',
                'vision' => 'nullable|string',
                'plan_estrategico' => 'nullable|string',
                'categoria' => 'nullable|string|max:50',
                'fecha_constitucion' => 'nullable|date',
                'email_contacto' => 'nullable|email|max:100',
                'activo' => 'boolean',
                'miembros' => 'array',
                'miembros.*.user_id' => 'required|exists:users,id',
                'miembros.*.rol' => 'required|string|max:50',
                'miembros.*.observaciones' => 'nullable|string',
            ]);

            $grupo = $this->grupoService->updateGrupo($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado exitosamente',
                'data' => $grupo,
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
                'message' => 'Error al actualizar grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->grupoService->deleteGrupo($id);

            return response()->json([
                'success' => true,
                'message' => 'Grupo eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addMember(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'rol' => 'required|string|max:50',
                'observaciones' => 'nullable|string',
            ]);

            $this->grupoService->addMemberToGrupo($id, $validated['user_id'], $validated['rol'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Miembro agregado exitosamente',
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
                'message' => 'Error al agregar miembro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeMember(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $this->grupoService->removeMemberFromGrupo($id, $validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Miembro removido exitosamente',
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
                'message' => 'Error al remover miembro',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->grupoService->activateGrupo($id);

            return response()->json([
                'success' => true,
                'message' => 'Grupo activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->grupoService->deactivateGrupo($id);

            return response()->json([
                'success' => true,
                'message' => 'Grupo desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllActive(): JsonResponse
    {
        try {
            $grupos = $this->grupoService->getAllActiveGrupos();

            return response()->json([
                'success' => true,
                'data' => $grupos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grupos activos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
