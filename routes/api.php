<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\SemilleroController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\ProductoCteiController;
use App\Http\Controllers\SoftwareRegistradoController;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\RevisionExpedienteController;
use App\Http\Controllers\CorteHistoricoController;
use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('auth:sanctum');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('auth:sanctum');
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
        Route::post('/{id}/roles', [UserController::class, 'assignRole'])->middleware('auth:sanctum');
        Route::delete('/{id}/roles', [UserController::class, 'removeRole'])->middleware('auth:sanctum');
        Route::post('/{id}/activate', [UserController::class, 'activate'])->middleware('auth:sanctum');
        Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::get('/active', [RoleController::class, 'getAllActive']);
            Route::get('/{id}', [RoleController::class, 'show']);
            Route::post('/', [RoleController::class, 'store']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);
            Route::post('/initialize-default', [RoleController::class, 'initializeDefaultRoles']);
        });

        Route::prefix('grupos')->group(function () {
            Route::get('/', [GrupoController::class, 'index']);
            Route::get('/active', [GrupoController::class, 'getAllActive']);
            Route::get('/{id}', [GrupoController::class, 'show']);
            Route::post('/', [GrupoController::class, 'store']);
            Route::put('/{id}', [GrupoController::class, 'update']);
            Route::delete('/{id}', [GrupoController::class, 'destroy']);
            Route::post('/{id}/members', [GrupoController::class, 'addMember']);
            Route::delete('/{id}/members', [GrupoController::class, 'removeMember']);
            Route::post('/{id}/activate', [GrupoController::class, 'activate']);
            Route::post('/{id}/deactivate', [GrupoController::class, 'deactivate']);
        });

        Route::prefix('semilleros')->group(function () {
            Route::get('/', [SemilleroController::class, 'index']);
            Route::get('/active', [SemilleroController::class, 'getAllActive']);
            Route::get('/{id}', [SemilleroController::class, 'show']);
            Route::post('/', [SemilleroController::class, 'store']);
            Route::put('/{id}', [SemilleroController::class, 'update']);
            Route::delete('/{id}', [SemilleroController::class, 'destroy']);
            Route::post('/{id}/members', [SemilleroController::class, 'addMember']);
            Route::delete('/{id}/members', [SemilleroController::class, 'removeMember']);
            Route::post('/{id}/grupos', [SemilleroController::class, 'articulateWithGrupo']);
            Route::delete('/{id}/grupos', [SemilleroController::class, 'removeGrupoArticulation']);
            Route::post('/{id}/activate', [SemilleroController::class, 'activate']);
            Route::post('/{id}/deactivate', [SemilleroController::class, 'deactivate']);
        });

        Route::prefix('proyectos')->group(function () {
            Route::get('/', [ProyectoController::class, 'index']);
            Route::get('/estado/{estado}', [ProyectoController::class, 'porEstado']);
            Route::get('/director/{directorId}', [ProyectoController::class, 'porDirector']);
            Route::get('/{id}', [ProyectoController::class, 'show']);
            Route::post('/', [ProyectoController::class, 'store']);
            Route::put('/{id}', [ProyectoController::class, 'update']);
            Route::delete('/{id}', [ProyectoController::class, 'destroy']);
            Route::post('/{id}/grupos', [ProyectoController::class, 'addGrupo']);
            Route::delete('/{id}/grupos', [ProyectoController::class, 'removeGrupo']);
            Route::post('/{id}/estado', [ProyectoController::class, 'cambiarEstado']);
            Route::post('/{id}/activate', [ProyectoController::class, 'activate']);
            Route::post('/{id}/deactivate', [ProyectoController::class, 'deactivate']);
        });

        Route::prefix('actividades')->group(function () {
            Route::get('/', [ActividadController::class, 'index']);
            Route::get('/proyecto/{proyectoId}', [ActividadController::class, 'porProyecto']);
            Route::get('/proyecto/{proyectoId}/arbol', [ActividadController::class, 'arbolPorProyecto']);
            Route::get('/proyecto/{proyectoId}/atrasadas', [ActividadController::class, 'atrasadasPorProyecto']);
            Route::get('/{id}', [ActividadController::class, 'show']);
            Route::post('/', [ActividadController::class, 'store']);
            Route::put('/{id}', [ActividadController::class, 'update']);
            Route::delete('/{id}', [ActividadController::class, 'destroy']);
            Route::post('/{id}/estado', [ActividadController::class, 'cambiarEstado']);
        });

        Route::prefix('productos')->group(function () {
            Route::get('/', [ProductoCteiController::class, 'index']);
            Route::get('/proximos-vencer', [ProductoCteiController::class, 'proximosAVencer']);
            Route::get('/fuera-ventana', [ProductoCteiController::class, 'fueraVentana']);
            Route::get('/{id}', [ProductoCteiController::class, 'show']);
            Route::post('/', [ProductoCteiController::class, 'store']);
            Route::put('/{id}', [ProductoCteiController::class, 'update']);
            Route::delete('/{id}', [ProductoCteiController::class, 'destroy']);
            Route::post('/{id}/autores', [ProductoCteiController::class, 'addAutor']);
            Route::delete('/{id}/autores', [ProductoCteiController::class, 'removeAutor']);
            Route::post('/{id}/estado', [ProductoCteiController::class, 'cambiarEstado']);
            Route::post('/{id}/activate', [ProductoCteiController::class, 'activate']);
            Route::post('/{id}/deactivate', [ProductoCteiController::class, 'deactivate']);
        });

        Route::prefix('software')->group(function () {
            Route::get('/', [SoftwareRegistradoController::class, 'index']);
            Route::get('/con-certificacion', [SoftwareRegistradoController::class, 'conCertificacion']);
            Route::get('/anio/{anio}', [SoftwareRegistradoController::class, 'porAnio']);
            Route::get('/{id}', [SoftwareRegistradoController::class, 'show']);
            Route::post('/', [SoftwareRegistradoController::class, 'store']);
            Route::put('/{id}', [SoftwareRegistradoController::class, 'update']);
            Route::delete('/{id}', [SoftwareRegistradoController::class, 'destroy']);
            Route::post('/fase/{faseId}/estado', [SoftwareRegistradoController::class, 'cambiarEstadoFase']);
            Route::post('/{softwareId}/certificacion', [SoftwareRegistradoController::class, 'agregarCertificacion']);
            Route::get('/{softwareId}/completitud', [SoftwareRegistradoController::class, 'verificarCompletitud']);
            Route::post('/{id}/activate', [SoftwareRegistradoController::class, 'activate']);
            Route::post('/{id}/deactivate', [SoftwareRegistradoController::class, 'deactivate']);
        });

        Route::prefix('evidencias')->group(function () {
            Route::get('/', [EvidenciaController::class, 'index']);
            Route::get('/{id}', [EvidenciaController::class, 'show']);
            Route::post('/', [EvidenciaController::class, 'store']);
            Route::put('/{id}', [EvidenciaController::class, 'update']);
            Route::delete('/{id}', [EvidenciaController::class, 'destroy']);
            Route::post('/{id}/validar-integridad', [EvidenciaController::class, 'validarIntegridad']);
            Route::post('/{id}/marcar-validada', [EvidenciaController::class, 'marcarValidada']);
            Route::get('/producto/{productoId}', [EvidenciaController::class, 'porProducto']);
        });

        Route::prefix('revisiones')->group(function () {
            Route::get('/', [RevisionExpedienteController::class, 'index']);
            Route::get('/{id}', [RevisionExpedienteController::class, 'show']);
            Route::post('/', [RevisionExpedienteController::class, 'store']);
            Route::put('/{id}', [RevisionExpedienteController::class, 'update']);
            Route::delete('/{id}', [RevisionExpedienteController::class, 'destroy']);
            Route::post('/{id}/enviar', [RevisionExpedienteController::class, 'enviar']);
            Route::post('/{id}/revisar', [RevisionExpedienteController::class, 'revisar']);
            Route::post('/{id}/aprobar', [RevisionExpedienteController::class, 'aprobar']);
            Route::post('/{id}/devolver', [RevisionExpedienteController::class, 'devolver']);
            Route::post('/{id}/rechazar', [RevisionExpedienteController::class, 'rechazar']);
            Route::post('/{id}/reportar', [RevisionExpedienteController::class, 'reportar']);
            Route::post('/{revisionId}/respuestas', [RevisionExpedienteController::class, 'registrarRespuesta']);
            Route::get('/historial/{productoId}', [RevisionExpedienteController::class, 'historial']);
            Route::get('/pendientes', [RevisionExpedienteController::class, 'pendientes']);
            Route::get('/completados', [RevisionExpedienteController::class, 'completados']);
        });

        Route::prefix('cortes')->group(function () {
            Route::get('/', [CorteHistoricoController::class, 'index']);
            Route::get('/{id}', [CorteHistoricoController::class, 'show']);
            Route::post('/', [CorteHistoricoController::class, 'store']);
            Route::put('/{id}', [CorteHistoricoController::class, 'update']);
            Route::delete('/{id}', [CorteHistoricoController::class, 'destroy']);
            Route::post('/{id}/cerrar', [CorteHistoricoController::class, 'cerrar']);
            Route::post('/{id}/reabrir', [CorteHistoricoController::class, 'reabrir']);
            Route::post('/{id}/archivar', [CorteHistoricoController::class, 'archivar']);
            Route::get('/abiertos', [CorteHistoricoController::class, 'abiertos']);
            Route::get('/cerrados', [CorteHistoricoController::class, 'cerrados']);
            Route::get('/recientes', [CorteHistoricoController::class, 'recientes']);
            Route::get('/ultimo', [CorteHistoricoController::class, 'ultimo']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('/resumen-grupos-semilleros', [DashboardController::class, 'resumenGruposSemilleros']);
            Route::get('/portafolio-ctei', [DashboardController::class, 'portafolioCtei']);
            Route::get('/alertas', [DashboardController::class, 'alertasTablero']);
            Route::get('/aportes-semilleros/{grupoId}', [DashboardController::class, 'aportesSemillerosProyectos']);
            Route::get('/metricas-corte-actual', [DashboardController::class, 'metricasCorteActual']);
            Route::get('/tendencias-produccion', [DashboardController::class, 'tendenciasProduccion']);
            Route::post('/comparar-cortes', [DashboardController::class, 'compararCortes']);
            Route::get('/indicadores-principales', [DashboardController::class, 'indicadoresPrincipales']);
        });
    });
});
