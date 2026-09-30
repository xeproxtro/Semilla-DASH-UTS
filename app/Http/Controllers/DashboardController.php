<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function resumenGruposSemilleros(): JsonResponse
    {
        try {
            $resumen = $this->dashboardService->getResumenGruposSemilleros();

            return response()->json([
                'success' => true,
                'data' => $resumen,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de grupos y semilleros',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function portafolioCtei(Request $request): JsonResponse
    {
        try {
            $filtros = [
                'categoria' => $request->get('categoria'),
                'subtipo' => $request->get('subtipo'),
                'estado' => $request->get('estado'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
            ];

            $portafolio = $this->dashboardService->getPortafolioCtei($filtros);

            return response()->json([
                'success' => true,
                'data' => $portafolio,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener portafolio CTeI',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function alertasTablero(Request $request): JsonResponse
    {
        try {
            $filtros = [
                'prioridad' => $request->get('prioridad'),
                'tipo' => $request->get('tipo'),
            ];

            $alertas = $this->dashboardService->getAlertasTablero($filtros);

            return response()->json([
                'success' => true,
                'data' => $alertas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas de tablero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function aportesSemillerosProyectos(int $grupoId): JsonResponse
    {
        try {
            $aportes = $this->dashboardService->getAportesSemillerosProyectos($grupoId);

            return response()->json([
                'success' => true,
                'data' => $aportes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener aportes de semilleros',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function metricasCorteActual(): JsonResponse
    {
        try {
            $metricas = $this->dashboardService->getMetricasCorteActual();

            return response()->json([
                'success' => true,
                'data' => $metricas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas del corte actual',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tendenciasProduccion(Request $request): JsonResponse
    {
        try {
            $meses = $request->get('meses', 12);
            $tendencias = $this->dashboardService->getTendenciasProduccion($meses);

            return response()->json([
                'success' => true,
                'data' => $tendencias,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tendencias de producción',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function compararCortes(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'corte_id_1' => 'required|exists:cortes_historicos,id',
                'corte_id_2' => 'required|exists:cortes_historicos,id|different:corte_id_1',
            ]);

            $comparacion = $this->dashboardService->compararCortes(
                $validated['corte_id_1'],
                $validated['corte_id_2']
            );

            return response()->json([
                'success' => true,
                'data' => $comparacion,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comparar cortes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function indicadoresPrincipales(): JsonResponse
    {
        try {
            $resumen = $this->dashboardService->getResumenGruposSemilleros();
            $portafolio = $this->dashboardService->getPortafolioCtei();
            $alertas = $this->dashboardService->getAlertasTablero();
            $metricas = $this->dashboardService->getMetricasCorteActual();

            return response()->json([
                'success' => true,
                'data' => [
                    'resumen_unidades' => $resumen,
                    'portafolio_ctei' => $portafolio,
                    'alertas' => $alertas,
                    'metricas_corte' => $metricas,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener indicadores principales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
