<?php

namespace App\Http\Controllers;

use App\Services\SoftwareRegistradoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SoftwareRegistradoController
{
    protected SoftwareRegistradoService $softwareService;

    public function __construct(SoftwareRegistradoService $softwareService)
    {
        $this->softwareService = $softwareService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'tipo' => $request->get('tipo'),
                'disponibilidad' => $request->get('disponibilidad'),
                'anio' => $request->get('anio'),
                'con_certificacion' => $request->get('con_certificacion'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $software = $this->softwareService->getAllSoftware($filters);

            return response()->json([
                'success' => true,
                'data' => $software->items(),
                'pagination' => [
                    'total' => $software->total(),
                    'per_page' => $software->perPage(),
                    'current_page' => $software->currentPage(),
                    'last_page' => $software->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $software = $this->softwareService->getSoftwareById($id);

            if (!$software) {
                return response()->json([
                    'success' => false,
                    'message' => 'Software no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $software,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'producto_id' => 'required|exists:productos_ctei,id',
                'nombre' => 'required|string|max:200',
                'version' => 'required|string|max:50',
                'anio_desarrollo' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'tipo' => 'required|in:Aplicacion,Sistema,Libreria,Framework,Plugin,Otro',
                'titular' => 'required|string|max:200',
                'licencia' => 'nullable|string|max:100',
                'disponibilidad' => 'required|in:Privado,Open Source,Comercial,Freeware',
                'url_repositorio' => 'nullable|url|max:500',
                'url_descarga' => 'nullable|url|max:500',
                'descripcion_tecnica' => 'nullable|string',
                'plataforma' => 'nullable|string|max:100',
                'lenguaje_programacion' => 'nullable|string|max:100',
                'lineas_codigo' => 'nullable|integer|min:0',
                'tiene_certificacion_innovacion' => 'boolean',
                'entidad_certificadora' => 'nullable|string|max:200',
                'fecha_certificacion' => 'nullable|date',
                'numero_registro_software' => 'nullable|string|max:50',
                'fecha_registro_dnda' => 'nullable|date',
                'nombre_soporte_logico' => 'nullable|string|max:200',
                'tipo_soporte_logico' => 'nullable|string|max:100',
                'url_soporte_logico' => 'nullable|url|max:500',
                'activo' => 'boolean',
                'fases' => 'array',
                'fases.*.fase' => 'required|in:Analisis,Diseño,Implementacion,Validacion',
                'fases.*.descripcion_fase' => 'nullable|string',
                'fases.*.fecha_inicio' => 'nullable|date',
                'fases.*.fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fases.*.estado' => 'nullable|in:Pendiente,En Progreso,Completado',
                'fases.*.documentacion' => 'nullable|string',
                'fases.*.evidencias' => 'nullable|string',
                'fases.*.responsable_id' => 'nullable|exists:users,id',
                'fases.*.observaciones' => 'nullable|string',
            ]);

            $software = $this->softwareService->createSoftware($validated);

            return response()->json([
                'success' => true,
                'message' => 'Software registrado exitosamente',
                'data' => $software,
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
                'message' => 'Error al registrar software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:200',
                'version' => 'sometimes|required|string|max:50',
                'anio_desarrollo' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'tipo' => 'sometimes|required|in:Aplicacion,Sistema,Libreria,Framework,Plugin,Otro',
                'titular' => 'sometimes|required|string|max:200',
                'licencia' => 'nullable|string|max:100',
                'disponibilidad' => 'sometimes|required|in:Privado,Open Source,Comercial,Freeware',
                'url_repositorio' => 'nullable|url|max:500',
                'url_descarga' => 'nullable|url|max:500',
                'descripcion_tecnica' => 'nullable|string',
                'plataforma' => 'nullable|string|max:100',
                'lenguaje_programacion' => 'nullable|string|max:100',
                'lineas_codigo' => 'nullable|integer|min:0',
                'tiene_certificacion_innovacion' => 'boolean',
                'entidad_certificadora' => 'nullable|string|max:200',
                'fecha_certificacion' => 'nullable|date',
                'numero_registro_software' => 'nullable|string|max:50',
                'fecha_registro_dnda' => 'nullable|date',
                'nombre_soporte_logico' => 'nullable|string|max:200',
                'tipo_soporte_logico' => 'nullable|string|max:100',
                'url_soporte_logico' => 'nullable|url|max:500',
                'activo' => 'boolean',
                'fases' => 'array',
                'fases.*.fase' => 'required|in:Analisis,Diseño,Implementacion,Validacion',
                'fases.*.descripcion_fase' => 'nullable|string',
                'fases.*.fecha_inicio' => 'nullable|date',
                'fases.*.fecha_fin' => 'nullable|date|after:fecha_inicio',
                'fases.*.estado' => 'nullable|in:Pendiente,En Progreso,Completado',
                'fases.*.documentacion' => 'nullable|string',
                'fases.*.evidencias' => 'nullable|string',
                'fases.*.responsable_id' => 'nullable|exists:users,id',
                'fases.*.observaciones' => 'nullable|string',
            ]);

            $software = $this->softwareService->updateSoftware($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Software actualizado exitosamente',
                'data' => $software,
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
                'message' => 'Error al actualizar software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->softwareService->deleteSoftware($id);

            return response()->json([
                'success' => true,
                'message' => 'Software eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiarEstadoFase(Request $request, int $faseId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:Pendiente,En Progreso,Completado',
            ]);

            $this->softwareService->cambiarEstadoFase($faseId, $validated['estado']);

            return response()->json([
                'success' => true,
                'message' => 'Estado de fase cambiado exitosamente',
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
                'message' => 'Error al cambiar estado de fase',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function agregarCertificacion(Request $request, int $softwareId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'entidad_certificadora' => 'required|string|max:200',
                'numero_certificado' => 'nullable|string|max:100',
                'fecha_emision' => 'required|date',
                'fecha_vigencia' => 'nullable|date|after:fecha_emision',
                'nivel_innovacion' => 'required|in:Bajo,Medio,Alto,Muy Alto',
                'descripcion_innovacion' => 'nullable|string',
                'url_certificado' => 'nullable|url|max:500',
                'ruta_documento' => 'nullable|string|max:500',
            ]);

            $this->softwareService->agregarCertificacion($softwareId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Certificación agregada exitosamente',
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
                'message' => 'Error al agregar certificación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verificarCompletitud(int $softwareId): JsonResponse
    {
        try {
            $resultado = $this->softwareService->verificarCompletitudFases($softwareId);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar completitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function conCertificacion(): JsonResponse
    {
        try {
            $software = $this->softwareService->getConCertificacion();

            return response()->json([
                'success' => true,
                'data' => $software,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener software con certificación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function porAnio(int $anio): JsonResponse
    {
        try {
            $software = $this->softwareService->getPorAnio($anio);

            return response()->json([
                'success' => true,
                'data' => $software,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener software por año',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->softwareService->activateSoftware($id);

            return response()->json([
                'success' => true,
                'message' => 'Software activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->softwareService->deactivateSoftware($id);

            return response()->json([
                'success' => true,
                'message' => 'Software desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar software',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
