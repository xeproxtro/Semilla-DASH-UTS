<?php

namespace App\Http\Controllers;

use App\Services\ActividadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ActividadController
{
    protected ActividadService $actividadService;

    public function __construct(ActividadService $actividadService)
    {
        $this->actividadService = $actividadService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'tipo' => $request->get('tipo'),
                'estado' => $request->get('estado'),
                'responsable_id' => $request->get('responsable_id'),
                'proyecto_id' => $request->get('proyecto_id'),
                'raiz' => $request->get('raiz'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $actividades = $this->actividadService->getAllActividades($filters);

            return response()->json([
                'success' => true,
                'data' => $actividades->items(),
                'pagination' => [
                    'total' => $actividades->total(),
                    'per_page' => $actividades->perPage(),
                    'current_page' => $actividades->currentPage(),
                    'last_page' => $actividades->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $actividad = $this->actividadService->getActividadById($id);

            if (!$actividad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Actividad no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $actividad,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividad',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'proyecto_id' => 'required|exists:proyectos,id',
                'codigo' => 'nullable|string|max:50',
                'nombre' => 'required|string|max:300',
                'descripcion' => 'nullable|string',
                'tipo' => 'required|in:Actividad,Hito,Entregable,Tarea',
                'actividad_padre_id' => 'nullable|exists:actividades,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fecha_fin_real' => 'nullable|date',
                'estado' => 'nullable|in:Pendiente,En Progreso,Completado,Atrasado,Cancelado',
                'porcentaje_avance' => 'nullable|integer|min:0|max:100',
                'responsable_id' => 'nullable|exists:users,id',
                'presupuesto_asignado' => 'nullable|numeric|min:0',
                'entregables_esperados' => 'nullable|string',
                'observaciones' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $actividad = $this->actividadService->createActividad($validated);

            return response()->json([
                'success' => true,
                'message' => 'Actividad creada exitosamente',
                'data' => $actividad,
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
                'message' => 'Error al crear actividad',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'nullable|string|max:50',
                'nombre' => 'sometimes|required|string|max:300',
                'descripcion' => 'nullable|string',
                'tipo' => 'sometimes|required|in:Actividad,Hito,Entregable,Tarea',
                'actividad_padre_id' => 'nullable|exists:actividades,id',
                'fecha_inicio' => 'sometimes|required|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fecha_fin_real' => 'nullable|date',
                'estado' => 'nullable|in:Pendiente,En Progreso,Completado,Atrasado,Cancelado',
                'porcentaje_avance' => 'nullable|integer|min:0|max:100',
                'responsable_id' => 'nullable|exists:users,id',
                'presupuesto_asignado' => 'nullable|numeric|min:0',
                'entregables_esperados' => 'nullable|string',
                'observaciones' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $actividad = $this->actividadService->updateActividad($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Actividad actualizada exitosamente',
                'data' => $actividad,
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
                'message' => 'Error al actualizar actividad',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->actividadService->deleteActividad($id);

            return response()->json([
                'success' => true,
                'message' => 'Actividad eliminada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar actividad',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:Pendiente,En Progreso,Completado,Atrasado,Cancelado',
            ]);

            $this->actividadService->cambiarEstadoActividad($id, $validated['estado']);

            return response()->json([
                'success' => true,
                'message' => 'Estado cambiado exitosamente',
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
                'message' => 'Error al cambiar estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function porProyecto(int $proyectoId): JsonResponse
    {
        try {
            $actividades = $this->actividadService->getActividadesPorProyecto($proyectoId);

            return response()->json([
                'success' => true,
                'data' => $actividades,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividades por proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function arbolPorProyecto(int $proyectoId): JsonResponse
    {
        try {
            $arbol = $this->actividadService->getArbolActividadesPorProyecto($proyectoId);

            return response()->json([
                'success' => true,
                'data' => $arbol,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener árbol de actividades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function atrasadasPorProyecto(int $proyectoId): JsonResponse
    {
        try {
            $actividades = $this->actividadService->getActividadesAtrasadas($proyectoId);

            return response()->json([
                'success' => true,
                'data' => $actividades,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividades atrasadas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
