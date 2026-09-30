<?php

namespace App\Http\Controllers;

use App\Services\SemilleroService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SemilleroController
{
    protected SemilleroService $semilleroService;

    public function __construct(SemilleroService $semilleroService)
    {
        $this->semilleroService = $semilleroService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'categoria' => $request->get('categoria'),
                'coordinador_id' => $request->get('coordinador_id'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $semilleros = $this->semilleroService->getAllSemilleros($filters);

            return response()->json([
                'success' => true,
                'data' => $semilleros->items(),
                'pagination' => [
                    'total' => $semilleros->total(),
                    'per_page' => $semilleros->perPage(),
                    'current_page' => $semilleros->currentPage(),
                    'last_page' => $semilleros->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener semilleros',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $semillero = $this->semilleroService->getSemilleroById($id);

            if (!$semillero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semillero no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $semillero,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener semillero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:20|unique:semilleros,codigo',
                'nombre' => 'required|string|max:200',
                'coordinador_id' => 'required|exists:users,id',
                'enfoque' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'plan_formativo' => 'nullable|string',
                'email_contacto' => 'nullable|email|max:100',
                'categoria' => 'nullable|string|max:50',
                'fecha_creacion' => 'nullable|date',
                'activo' => 'boolean',
                'integrantes' => 'array',
                'integrantes.*.user_id' => 'required|exists:users,id',
                'integrantes.*.rol' => 'required|string|max:50',
                'integrantes.*.observaciones' => 'nullable|string',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.observaciones' => 'nullable|string',
            ]);

            $semillero = $this->semilleroService->createSemillero($validated);

            return response()->json([
                'success' => true,
                'message' => 'Semillero creado exitosamente',
                'data' => $semillero,
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
                'message' => 'Error al crear semillero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'sometimes|required|string|max:20|unique:semilleros,codigo,' . $id,
                'nombre' => 'sometimes|required|string|max:200',
                'coordinador_id' => 'sometimes|required|exists:users,id',
                'enfoque' => 'sometimes|required|string|max:100',
                'descripcion' => 'nullable|string',
                'plan_formativo' => 'nullable|string',
                'email_contacto' => 'nullable|email|max:100',
                'categoria' => 'nullable|string|max:50',
                'fecha_creacion' => 'nullable|date',
                'activo' => 'boolean',
                'integrantes' => 'array',
                'integrantes.*.user_id' => 'required|exists:users,id',
                'integrantes.*.rol' => 'required|string|max:50',
                'integrantes.*.observaciones' => 'nullable|string',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.observaciones' => 'nullable|string',
            ]);

            $semillero = $this->semilleroService->updateSemillero($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Semillero actualizado exitosamente',
                'data' => $semillero,
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
                'message' => 'Error al actualizar semillero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->semilleroService->deleteSemillero($id);

            return response()->json([
                'success' => true,
                'message' => 'Semillero eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar semillero',
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

            $this->semilleroService->addMemberToSemillero($id, $validated['user_id'], $validated['rol'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Integrante agregado exitosamente',
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
                'message' => 'Error al agregar integrante',
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

            $this->semilleroService->removeMemberFromSemillero($id, $validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Integrante removido exitosamente',
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
                'message' => 'Error al remover integrante',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function articulateWithGrupo(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grupo_id' => 'required|exists:grupos,id',
                'observaciones' => 'nullable|string',
            ]);

            $this->semilleroService->articulateSemilleroWithGrupo($id, $validated['grupo_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Semillero articulado con grupo exitosamente',
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
                'message' => 'Error al articular semillero con grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeGrupoArticulation(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grupo_id' => 'required|exists:grupos,id',
            ]);

            $this->semilleroService->removeGrupoArticulation($id, $validated['grupo_id']);

            return response()->json([
                'success' => true,
                'message' => 'Articulación removida exitosamente',
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
                'message' => 'Error al remover articulación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->semilleroService->activateSemillero($id);

            return response()->json([
                'success' => true,
                'message' => 'Semillero activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar semillero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->semilleroService->deactivateSemillero($id);

            return response()->json([
                'success' => true,
                'message' => 'Semillero desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar semillero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllActive(): JsonResponse
    {
        try {
            $semilleros = $this->semilleroService->getAllActiveSemilleros();

            return response()->json([
                'success' => true,
                'data' => $semilleros,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener semilleros activos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
