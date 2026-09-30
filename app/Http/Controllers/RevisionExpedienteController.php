<?php

namespace App\Http\Controllers;

use App\Services\RevisionExpedienteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RevisionExpedienteController
{
    protected RevisionExpedienteService $revisionService;

    public function __construct(RevisionExpedienteService $revisionService)
    {
        $this->revisionService = $revisionService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'estado' => $request->get('estado'),
                'producto_id' => $request->get('producto_id'),
                'lista_id' => $request->get('lista_id'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $revisiones = $this->revisionService->getAllRevisiones($filters);

            return response()->json([
                'success' => true,
                'data' => $revisiones->items(),
                'pagination' => [
                    'total' => $revisiones->total(),
                    'per_page' => $revisiones->perPage(),
                    'current_page' => $revisiones->currentPage(),
                    'last_page' => $revisiones->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener revisiones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $revision = $this->revisionService->getRevisionById($id);

            if (!$revision) {
                return response()->json([
                    'success' => false,
                    'message' => 'Revisión no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $revision,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener revisión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'producto_id' => 'required|exists:productos_ctei,id',
                'lista_chequeo_id' => 'nullable|exists:listas_chequeo,id',
                'estado' => 'nullable|in:Borrador,Enviado,Devuelto,Revisado,Avalado,Reportado,Validado_Externamente,Rechazado,Anulado',
                'observaciones' => 'nullable|string',
                'porcentaje_completitud' => 'nullable|integer|min:0|max:100',
                'cumple_requisitos' => 'boolean',
                'activo' => 'boolean',
            ]);

            $revision = $this->revisionService->createRevision($validated);

            return response()->json([
                'success' => true,
                'message' => 'Revisión creada exitosamente',
                'data' => $revision,
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
                'message' => 'Error al crear revisión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'lista_chequeo_id' => 'nullable|exists:listas_chequeo,id',
                'estado' => 'nullable|in:Borrador,Enviado,Devuelto,Revisado,Avalado,Reportado,Validado_Externamente,Rechazado,Anulado',
                'observaciones' => 'nullable|string',
                'porcentaje_completitud' => 'nullable|integer|min:0|max:100',
                'cumple_requisitos' => 'boolean',
                'activo' => 'boolean',
            ]);

            $revision = $this->revisionService->updateRevision($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Revisión actualizada exitosamente',
                'data' => $revision,
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
                'message' => 'Error al actualizar revisión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->revisionService->deleteRevision($id);

            return response()->json([
                'success' => true,
                'message' => 'Revisión eliminada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar revisión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enviar(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'nullable|string',
            ]);

            $this->revisionService->enviarRevision($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Revisión enviada exitosamente',
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
                'message' => 'Error al enviar revisión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function revisar(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'nullable|string',
            ]);

            $this->revisionService->revisarCompletitud($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Completitud revisada exitosamente',
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
                'message' => 'Error al revisar completitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function aprobar(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'nullable|string',
            ]);

            $this->revisionService->aprobarExpediente($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Expediente aprobado exitosamente',
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
                'message' => 'Error al aprobar expediente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function devolver(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'required|string',
            ]);

            $this->revisionService->devolverExpediente($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Expediente devuelto exitosamente',
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
                'message' => 'Error al devolver expediente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rechazar(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'required|string',
            ]);

            $this->revisionService->rechazarExpediente($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Expediente rechazado exitosamente',
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
                'message' => 'Error al rechazar expediente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportar(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'observaciones' => 'nullable|string',
            ]);

            $this->revisionService->reportarExpediente($id, $validated['user_id'], $validated['observaciones']);

            return response()->json([
                'success' => true,
                'message' => 'Expediente reportado exitosamente',
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
                'message' => 'Error al reportar expediente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registrarRespuesta(Request $request, int $revisionId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'item_lista_id' => 'required|exists:items_lista_chequeo,id',
                'respuesta' => 'nullable|string',
                'cumple' => 'required|boolean',
                'observaciones' => 'nullable|string',
                'respondido_por' => 'nullable|exists:users,id',
            ]);

            $this->revisionService->registrarRespuesta($revisionId, $validated['item_lista_id'], $validated);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta registrada exitosamente',
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
                'message' => 'Error al registrar respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function historial(int $productoId): JsonResponse
    {
        try {
            $historial = $this->revisionService->getHistorialProducto($productoId);

            return response()->json([
                'success' => true,
                'data' => $historial,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pendientes(): JsonResponse
    {
        try {
            $revisiones = $this->revisionService->getRevisionesPendientes();

            return response()->json([
                'success' => true,
                'data' => $revisiones,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener revisiones pendientes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completados(): JsonResponse
    {
        try {
            $revisiones = $this->revisionService->getRevisionesCompletados();

            return response()->json([
                'success' => true,
                'data' => $revisiones,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener revisiones completados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
