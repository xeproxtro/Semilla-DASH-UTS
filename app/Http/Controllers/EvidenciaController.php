<?php

namespace App\Http\Controllers;

use App\Services\EvidenciaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EvidenciaController
{
    protected EvidenciaService $evidenciaService;

    public function __construct(EvidenciaService $evidenciaService)
    {
        $this->evidenciaService = $evidenciaService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'tipo' => $request->get('tipo'),
                'producto_id' => $request->get('producto_id'),
                'nivel_acceso' => $request->get('nivel_acceso'),
                'subido_por' => $request->get('subido_por'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $evidencias = $this->evidenciaService->getAllEvidencias($filters);

            return response()->json([
                'success' => true,
                'data' => $evidencias->items(),
                'pagination' => [
                    'total' => $evidencias->total(),
                    'per_page' => $evidencias->perPage(),
                    'current_page' => $evidencias->currentPage(),
                    'last_page' => $evidencias->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener evidencias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $evidencia = $this->evidenciaService->getEvidenciaById($id);

            if (!$evidencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evidencia no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $evidencia,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener evidencia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'producto_id' => 'required|exists:productos_ctei,id',
                'subido_por' => 'required|exists:users,id',
                'tipo' => 'required|in:Archivo,Enlace,Documento,Video,Audio,Imagen,Otro',
                'nombre' => 'required|string|max:300',
                'ruta_archivo' => 'nullable|string|max:500',
                'url_enlace' => 'nullable|url|max:500',
                'version' => 'nullable|string|max:20',
                'descripcion' => 'nullable|string',
                'mime_type' => 'nullable|string|max:100',
                'tamano_bytes' => 'nullable|integer|min:0',
                'nivel_acceso' => 'required|in:Privado,Interno,Publico',
                'fecha_documento' => 'nullable|date',
                'registro_soportado' => 'nullable|string|max:100',
                'validado' => 'boolean',
                'activo' => 'boolean',
                'archivo' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,png',
            ]);

            $evidencia = $this->evidenciaService->createEvidencia($validated);

            return response()->json([
                'success' => true,
                'message' => 'Evidencia creada exitosamente',
                'data' => $evidencia,
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
                'message' => 'Error al crear evidencia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tipo' => 'sometimes|required|in:Archivo,Enlace,Documento,Video,Audio,Imagen,Otro',
                'nombre' => 'sometimes|required|string|max:300',
                'ruta_archivo' => 'nullable|string|max:500',
                'url_enlace' => 'nullable|url|max:500',
                'version' => 'nullable|string|max:20',
                'descripcion' => 'nullable|string',
                'mime_type' => 'nullable|string|max:100',
                'tamano_bytes' => 'nullable|integer|min:0',
                'nivel_acceso' => 'sometimes|required|in:Privado,Interno,Publico',
                'fecha_documento' => 'nullable|date',
                'registro_soportado' => 'nullable|string|max:100',
                'validado' => 'boolean',
                'activo' => 'boolean',
                'archivo' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,png',
            ]);

            $evidencia = $this->evidenciaService->updateEvidencia($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Evidencia actualizada exitosamente',
                'data' => $evidencia,
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
                'message' => 'Error al actualizar evidencia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->evidenciaService->deleteEvidencia($id);

            return response()->json([
                'success' => true,
                'message' => 'Evidencia eliminada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar evidencia',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function validarIntegridad(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'hash' => 'required|string',
            ]);

            $valido = $this->evidenciaService->validarIntegridad($id, $validated['hash']);

            return response()->json([
                'success' => true,
                'valido' => $valido,
                'message' => $valido ? 'Integridad verificada' : 'Integridad no coincide',
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
                'message' => 'Error al validar integridad',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function marcarValidada(int $id): JsonResponse
    {
        try {
            $result = $this->evidenciaService->marcarValidada($id);

            return response()->json([
                'success' => true,
                'message' => 'Evidencia marcada como validada',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar evidencia como validada',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function porProducto(int $productoId): JsonResponse
    {
        try {
            $evidencias = $this->evidenciaService->getByProducto($productoId);

            return response()->json([
                'success' => true,
                'data' => $evidencias,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener evidencias del producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
