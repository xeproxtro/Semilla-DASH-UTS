<?php

namespace App\Http\Controllers;

use App\Services\CorteHistoricoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CorteHistoricoController
{
    protected CorteHistoricoService $corteService;

    public function __construct(CorteHistoricoService $corteService)
    {
        $this->corteService = $corteService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'estado' => $request->get('estado'),
                'version_catalogo_id' => $request->get('version_catalogo_id'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $cortes = $this->corteService->getAllCortes($filters);

            return response()->json([
                'success' => true,
                'data' => $cortes->items(),
                'pagination' => [
                    'total' => $cortes->total(),
                    'per_page' => $cortes->perPage(),
                    'current_page' => $cortes->currentPage(),
                    'last_page' => $cortes->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cortes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $corte = $this->corteService->getCorteById($id);

            if (!$corte) {
                return response()->json([
                    'success' => false,
                    'message' => 'Corte no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $corte,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:200',
                'fecha_inicio_periodo' => 'required|date',
                'fecha_fin_periodo' => 'required|date|after:fecha_inicio_periodo',
                'version_catalogo_id' => 'required|exists:versiones_catalogo,id',
                'creado_por' => 'required|exists:users,id',
                'descripcion' => 'nullable|string',
                'estado' => 'nullable|in:Abierto,Cerrado,Archivado',
                'instituciones_incluidas' => 'nullable|array',
                'grupos_incluidos' => 'nullable|array',
                'semilleros_incluidos' => 'nullable|array',
                'reglas_ventanas' => 'nullable|array',
                'activo' => 'boolean',
            ]);

            $corte = $this->corteService->createCorte($validated);

            return response()->json([
                'success' => true,
                'message' => 'Corte creado exitosamente',
                'data' => $corte,
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
                'message' => 'Error al crear corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:200',
                'fecha_inicio_periodo' => 'nullable|date',
                'fecha_fin_periodo' => 'nullable|date|after:fecha_inicio_periodo',
                'version_catalogo_id' => 'nullable|exists:versiones_catalogo,id',
                'descripcion' => 'nullable|string',
                'estado' => 'nullable|in:Abierto,Cerrado,Archivado',
                'instituciones_incluidas' => 'nullable|array',
                'grupos_incluidos' => 'nullable|array',
                'semilleros_incluidos' => 'nullable|array',
                'reglas_ventanas' => 'nullable|array',
                'activo' => 'boolean',
            ]);

            $corte = $this->corteService->updateCorte($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Corte actualizado exitosamente',
                'data' => $corte,
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
                'message' => 'Error al actualizar corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->corteService->deleteCorte($id);

            return response()->json([
                'success' => true,
                'message' => 'Corte eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cerrar(int $id): JsonResponse
    {
        try {
            $this->corteService->cerrarCorte($id);

            return response()->json([
                'success' => true,
                'message' => 'Corte cerrado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reabrir(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $nuevoCorte = $this->corteService->reabrirCorte($id, $validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Corte reabierto exitosamente',
                'data' => $nuevoCorte,
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
                'message' => 'Error al reabrir corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function archivar(int $id): JsonResponse
    {
        try {
            $this->corteService->archivarCorte($id);

            return response()->json([
                'success' => true,
                'message' => 'Corte archivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al archivar corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function abiertos(): JsonResponse
    {
        try {
            $cortes = $this->corteService->getAbiertos();

            return response()->json([
                'success' => true,
                'data' => $cortes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cortes abiertos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cerrados(): JsonResponse
    {
        try {
            $cortes = $this->corteService->getCerrados();

            return response()->json([
                'success' => true,
                'data' => $cortes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cortes cerrados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function recientes(Request $request): JsonResponse
    {
        try {
            $dias = $request->get('dias', 30);
            $cortes = $this->corteService->getRecientes($dias);

            return response()->json([
                'success' => true,
                'data' => $cortes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cortes recientes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ultimo(): JsonResponse
    {
        try {
            $corte = $this->corteService->getUltimoCorte();

            return response()->json([
                'success' => true,
                'data' => $corte,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener último corte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
