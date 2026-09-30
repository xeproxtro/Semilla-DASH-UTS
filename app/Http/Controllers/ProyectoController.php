<?php

namespace App\Http\Controllers;

use App\Services\ProyectoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProyectoController
{
    protected ProyectoService $proyectoService;

    public function __construct(ProyectoService $proyectoService)
    {
        $this->proyectoService = $proyectoService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'tipo' => $request->get('tipo'),
                'estado' => $request->get('estado'),
                'director_id' => $request->get('director_id'),
                'linea_id' => $request->get('linea_id'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $proyectos = $this->proyectoService->getAllProyectos($filters);

            return response()->json([
                'success' => true,
                'data' => $proyectos->items(),
                'pagination' => [
                    'total' => $proyectos->total(),
                    'per_page' => $proyectos->perPage(),
                    'current_page' => $proyectos->currentPage(),
                    'last_page' => $proyectos->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyectos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $proyecto = $this->proyectoService->getProyectoById($id);

            if (!$proyecto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyecto no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $proyecto,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:proyectos,codigo',
                'titulo' => 'required|string|max:500',
                'objetivo_general' => 'required|string',
                'objetivos_especificos' => 'nullable|string',
                'tipo' => 'required|in:investigacion_desarrollo,investigacion_creacion,idi,extension,formativo',
                'convocatoria' => 'nullable|string|max:100',
                'linea_investigacion_id' => 'nullable|exists:lineas_investigacion,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fecha_fin_real' => 'nullable|date',
                'estado' => 'nullable|in:Propuesto,Aprobado,En Ejecucion,Suspendido,Finalizado,Cancelado',
                'presupuesto_total' => 'nullable|numeric|min:0',
                'director' => 'nullable|string|max:200',
                'director_id' => 'required|exists:users,id',
                'resumen' => 'nullable|string',
                'palabras_clave' => 'nullable|string',
                'url_externa' => 'nullable|url|max:500',
                'activo' => 'boolean',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.rol' => 'required|in:Principal,Asociado,Colaborador',
                'grupos.*.observaciones' => 'nullable|string',
                'semilleros' => 'array',
                'semilleros.*.semillero_id' => 'required|exists:semilleros,id',
                'semilleros.*.rol' => 'required|in:Principal,Asociado,Colaborador',
                'semilleros.*.observaciones' => 'nullable|string',
                'participantes' => 'array',
                'participantes.*.user_id' => 'required|exists:users,id',
                'participantes.*.rol' => 'required|in:Director,Coinvestigador,Investigador,Asistente,Estudiante,Tecnico,Administrativo',
                'participantes.*.horas_dedicacion' => 'nullable|integer|min:0',
                'participantes.*.presupuesto_asignado' => 'nullable|numeric|min:0',
                'participantes.*.fecha_inicio' => 'required|date',
                'participantes.*.fecha_fin' => 'nullable|date|after:fecha_inicio',
                'participantes.*.observaciones' => 'nullable|string',
            ]);

            $proyecto = $this->proyectoService->createProyecto($validated);

            return response()->json([
                'success' => true,
                'message' => 'Proyecto creado exitosamente',
                'data' => $proyecto,
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
                'message' => 'Error al crear proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'codigo' => 'sometimes|required|string|max:50|unique:proyectos,codigo,' . $id,
                'titulo' => 'sometimes|required|string|max:500',
                'objetivo_general' => 'sometimes|required|string',
                'objetivos_especificos' => 'nullable|string',
                'tipo' => 'sometimes|required|in:investigacion_desarrollo,investigacion_creacion,idi,extension,formativo',
                'convocatoria' => 'nullable|string|max:100',
                'linea_investigacion_id' => 'nullable|exists:lineas_investigacion,id',
                'fecha_inicio' => 'sometimes|required|date',
                'fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fecha_fin_real' => 'nullable|date',
                'estado' => 'nullable|in:Propuesto,Aprobado,En Ejecucion,Suspendido,Finalizado,Cancelado',
                'presupuesto_total' => 'nullable|numeric|min:0',
                'director' => 'nullable|string|max:200',
                'director_id' => 'sometimes|required|exists:users,id',
                'resumen' => 'nullable|string',
                'palabras_clave' => 'nullable|string',
                'url_externa' => 'nullable|url|max:500',
                'activo' => 'boolean',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.rol' => 'required|in:Principal,Asociado,Colaborador',
                'grupos.*.observaciones' => 'nullable|string',
                'semilleros' => 'array',
                'semilleros.*.semillero_id' => 'required|exists:semilleros,id',
                'semilleros.*.rol' => 'required|in:Principal,Asociado,Colaborador',
                'semilleros.*.observaciones' => 'nullable|string',
                'participantes' => 'array',
                'participantes.*.user_id' => 'required|exists:users,id',
                'participantes.*.rol' => 'required|in:Director,Coinvestigador,Investigador,Asistente,Estudiante,Tecnico,Administrativo',
                'participantes.*.horas_dedicacion' => 'nullable|integer|min:0',
                'participantes.*.presupuesto_asignado' => 'nullable|numeric|min:0',
                'participantes.*.fecha_inicio' => 'required|date',
                'participantes.*.fecha_fin' => 'nullable|date|after:fecha_inicio',
                'participantes.*.observaciones' => 'nullable|string',
            ]);

            $proyecto = $this->proyectoService->updateProyecto($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Proyecto actualizado exitosamente',
                'data' => $proyecto,
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
                'message' => 'Error al actualizar proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->proyectoService->deleteProyecto($id);

            return response()->json([
                'success' => true,
                'message' => 'Proyecto eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addGrupo(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grupo_id' => 'required|exists:grupos,id',
                'rol' => 'required|in:Principal,Asociado,Colaborador',
                'observaciones' => 'nullable|string',
            ]);

            $this->proyectoService->addGrupoToProyecto($id, $validated['grupo_id'], $validated['rol'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Grupo agregado exitosamente',
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
                'message' => 'Error al agregar grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeGrupo(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grupo_id' => 'required|exists:grupos,id',
            ]);

            $this->proyectoService->removeGrupoFromProyecto($id, $validated['grupo_id']);

            return response()->json([
                'success' => true,
                'message' => 'Grupo removido exitosamente',
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
                'message' => 'Error al remover grupo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:Propuesto,Aprobado,En Ejecucion,Suspendido,Finalizado,Cancelado',
            ]);

            $this->proyectoService->cambiarEstadoProyecto($id, $validated['estado']);

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

    public function activate(int $id): JsonResponse
    {
        try {
            $this->proyectoService->activateProyecto($id);

            return response()->json([
                'success' => true,
                'message' => 'Proyecto activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->proyectoService->deactivateProyecto($id);

            return response()->json([
                'success' => true,
                'message' => 'Proyecto desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar proyecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function porEstado(Request $request, string $estado): JsonResponse
    {
        try {
            $proyectos = $this->proyectoService->getProyectosPorEstado($estado);

            return response()->json([
                'success' => true,
                'data' => $proyectos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyectos por estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function porDirector(int $directorId): JsonResponse
    {
        try {
            $proyectos = $this->proyectoService->getProyectosPorDirector($directorId);

            return response()->json([
                'success' => true,
                'data' => $proyectos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyectos por director',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
