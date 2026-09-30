<?php

namespace App\Http\Controllers;

use App\Services\ProductoCteiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProductoCteiController
{
    protected ProductoCteiService $productoService;

    public function __construct(ProductoCteiService $productoService)
    {
        $this->productoService = $productoService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'activo' => $request->get('activo'),
                'estado' => $request->get('estado'),
                'subtipo_id' => $request->get('subtipo_id'),
                'autor_id' => $request->get('autor_id'),
                'grupo_id' => $request->get('grupo_id'),
                'semillero_id' => $request->get('semillero_id'),
                'proyecto_id' => $request->get('proyecto_id'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
                'visible_publicamente' => $request->get('visible_publicamente'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $productos = $this->productoService->getAllProductos($filters);

            return response()->json([
                'success' => true,
                'data' => $productos->items(),
                'pagination' => [
                    'total' => $productos->total(),
                    'per_page' => $productos->perPage(),
                    'current_page' => $productos->currentPage(),
                    'last_page' => $productos->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->getProductoById($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $producto,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:500',
                'subtipo_id' => 'required|exists:subtipos_producto,id',
                'descripcion' => 'nullable|string',
                'fecha_publicacion' => 'required|date',
                'estado' => 'nullable|in:Borrador,Enviado,Devuelto,Revisado,Avalado,Reportado',
                'doi' => 'nullable|string|max:100|unique:productos_ctei,doi',
                'isbn' => 'nullable|string|max:20|unique:productos_ctei,isbn',
                'issn' => 'nullable|string|max:20|unique:productos_ctei,issn',
                'patente' => 'nullable|string|max:50|unique:productos_ctei,patente',
                'registro' => 'nullable|string|max:50|unique:productos_ctei,registro',
                'handle' => 'nullable|string|max:100|unique:productos_ctei,handle',
                'url' => 'nullable|url|max:500',
                'palabras_clave' => 'nullable|string',
                'idioma' => 'nullable|string|max:50',
                'ciudad' => 'nullable|string|max:100',
                'pais' => 'nullable|string|max:50',
                'presupuesto_inversion' => 'nullable|numeric|min:0',
                'moneda' => 'nullable|string|max:10',
                'visible_publicamente' => 'boolean',
                'activo' => 'boolean',
                'autores' => 'array',
                'autores.*.user_id' => 'required|exists:users,id',
                'autores.*.orden_autoria' => 'required|integer|min:0',
                'autores.*.rol_autoria' => 'required|string|max:50',
                'autores.*.autor_correspondencia' => 'boolean',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.grupo_principal' => 'boolean',
                'semilleros' => 'array',
                'semilleros.*.semillero_id' => 'required|exists:semilleros,id',
                'semilleros.*.semillero_principal' => 'boolean',
                'proyectos' => 'array',
                'proyectos.*.proyecto_id' => 'required|exists:proyectos,id',
                'proyectos.*.resultado_principal' => 'boolean',
                'instituciones' => 'array',
                'instituciones.*.institucion_id' => 'required|exists:instituciones,id',
                'instituciones.*.tipo_participacion' => 'required|in:Productora,Coeditora,Financiadora',
                'instituciones.*.institucion_principal' => 'boolean',
            ]);

            $producto = $this->productoService->createProducto($validated);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $producto,
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
                'message' => 'Error al crear producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'sometimes|required|string|max:500',
                'subtipo_id' => 'sometimes|required|exists:subtipos_producto,id',
                'descripcion' => 'nullable|string',
                'fecha_publicacion' => 'sometimes|required|date',
                'estado' => 'nullable|in:Borrador,Enviado,Devuelto,Revisado,Avalado,Reportado',
                'doi' => 'nullable|string|max:100|unique:productos_ctei,doi,' . $id,
                'isbn' => 'nullable|string|max:20|unique:productos_ctei,isbn,' . $id,
                'issn' => 'nullable|string|max:20|unique:productos_ctei,issn,' . $id,
                'patente' => 'nullable|string|max:50|unique:productos_ctei,patente,' . $id,
                'registro' => 'nullable|string|max:50|unique:productos_ctei,registro,' . $id,
                'handle' => 'nullable|string|max:100|unique:productos_ctei,handle,' . $id,
                'url' => 'nullable|url|max:500',
                'palabras_clave' => 'nullable|string',
                'idioma' => 'nullable|string|max:50',
                'ciudad' => 'nullable|string|max:100',
                'pais' => 'nullable|string|max:50',
                'presupuesto_inversion' => 'nullable|numeric|min:0',
                'moneda' => 'nullable|string|max:10',
                'visible_publicamente' => 'boolean',
                'activo' => 'boolean',
                'autores' => 'array',
                'autores.*.user_id' => 'required|exists:users,id',
                'autores.*.orden_autoria' => 'required|integer|min:0',
                'autores.*.rol_autoria' => 'required|string|max:50',
                'autores.*.autor_correspondencia' => 'boolean',
                'grupos' => 'array',
                'grupos.*.grupo_id' => 'required|exists:grupos,id',
                'grupos.*.grupo_principal' => 'boolean',
                'semilleros' => 'array',
                'semilleros.*.semillero_id' => 'required|exists:semilleros,id',
                'semilleros.*.semillero_principal' => 'boolean',
                'proyectos' => 'array',
                'proyectos.*.proyecto_id' => 'required|exists:proyectos,id',
                'proyectos.*.resultado_principal' => 'boolean',
                'instituciones' => 'array',
                'instituciones.*.institucion_id' => 'required|exists:instituciones,id',
                'instituciones.*.tipo_participacion' => 'required|in:Productora,Coeditora,Financiadora',
                'instituciones.*.institucion_principal' => 'boolean',
            ]);

            $producto = $this->productoService->updateProducto($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto,
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
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->productoService->deleteProducto($id);

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAutor(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'orden_autoria' => 'required|integer|min:0',
                'rol_autoria' => 'required|string|max:50',
                'autor_correspondencia' => 'boolean',
            ]);

            $this->productoService->addAutorToProducto($id, $validated['user_id'], $validated['orden_autoria'], $validated['rol_autoria'], $validated['autor_correspondencia']);

            return response()->json([
                'success' => true,
                'message' => 'Autor agregado exitosamente',
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
                'message' => 'Error al agregar autor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeAutor(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $this->productoService->removeAutorFromProducto($id, $validated['user_id']);

            return response()->json([
                'success' => true,
                'message' => 'Autor removido exitosamente',
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
                'message' => 'Error al remover autor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:Borrador,Enviado,Devuelto,Revisado,Avalado,Reportado',
            ]);

            $this->productoService->cambiarEstadoProducto($id, $validated['estado']);

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

    public function proximosAVencer(Request $request): JsonResponse
    {
        try {
            $dias = $request->get('dias', 90);
            $productos = $this->productoService->getProximosAVencer($dias);

            return response()->json([
                'success' => true,
                'data' => $productos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener próximos a vencer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fueraVentana(): JsonResponse
    {
        try {
            $productos = $this->productoService->getFueraVentana();

            return response()->json([
                'success' => true,
                'data' => $productos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener fuera de ventana',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->productoService->activateProducto($id);

            return response()->json([
                'success' => true,
                'message' => 'Producto activado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(int $id): JsonResponse
    {
        try {
            $this->productoService->deactivateProducto($id);

            return response()->json([
                'success' => true,
                'message' => 'Producto desactivado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
