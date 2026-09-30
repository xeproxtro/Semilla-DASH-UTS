<?php

namespace App\Services;

use App\Repositories\GrupoRepository;
use App\Repositories\SemilleroRepository;
use App\Repositories\ProductoCteiRepository;
use App\Repositories\ProyectoRepository;
use App\Repositories\CorteHistoricoRepository;
use App\Repositories\AlertaTableroRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardService
{
    protected GrupoRepository $grupoRepository;
    protected SemilleroRepository $semilleroRepository;
    protected ProductoCteiRepository $productoRepository;
    protected ProyectoRepository $proyectoRepository;
    protected CorteHistoricoRepository $corteRepository;
    protected AlertaTableroRepository $alertaRepository;

    public function __construct(
        GrupoRepository $grupoRepository,
        SemilleroRepository $semilleroRepository,
        ProductoCteiRepository $productoRepository,
        ProyectoRepository $proyectoRepository,
        CorteHistoricoRepository $corteRepository,
        AlertaTableroRepository $alertaRepository
    ) {
        $this->grupoRepository = $grupoRepository;
        $this->semilleroRepository = $semilleroRepository;
        $this->productoRepository = $productoRepository;
        $this->proyectoRepository = $proyectoRepository;
        $this->corteRepository = $corteRepository;
        $this->alertaRepository = $alertaRepository;
    }

    public function getResumenGruposSemilleros(): array
    {
        try {
            $gruposActivos = $this->grupoRepository->getAllActive();
            $semillerosActivos = $this->semilleroRepository->getAllActive();
            
            $gruposPorCategoria = [];
            foreach ($gruposActivos as $grupo) {
                $categoria = $grupo['categoria'] ?? 'Sin Categoría';
                $gruposPorCategoria[$categoria] = ($gruposPorCategoria[$categoria] ?? 0) + 1;
            }

            $semillerosPorCategoria = [];
            foreach ($semillerosActivos as $semillero) {
                $categoria = $semillero['categoria'] ?? 'Sin Categoría';
                $semillerosPorCategoria[$categoria] = ($semillerosPorCategoria[$categoria] ?? 0) + 1;
            }

            return [
                'grupos' => [
                    'total' => count($gruposActivos),
                    'por_categoria' => $gruposPorCategoria,
                    'recientes' => array_slice($gruposActivos, 0, 5),
                ],
                'semilleros' => [
                    'total' => count($semillerosActivos),
                    'por_categoria' => $semillerosPorCategoria,
                    'recientes' => array_slice($semillerosActivos, 0, 5),
                ],
                'total_unidades' => count($gruposActivos) + count($semillerosActivos),
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener resumen grupos y semilleros: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPortafolioCtei(array $filtros = []): array
    {
        try {
            $productos = $this->productoRepository->all([
                'activo' => true,
                ...$filtros,
            ])->items();

            $agrupadoPorCategoria = [];
            $agrupadoPorSubtipo = [];
            $agrupadoPorEstado = [];

            foreach ($productos as $producto) {
                $categoria = $producto['subtipo']['categoria']['tipo_categoria'] ?? 'Sin Categoría';
                $subtipo = $producto['subtipo']['nombre'] ?? 'Sin Subtipo';
                $estado = $producto['estado'] ?? 'Sin Estado';

                $agrupadoPorCategoria[$categoria] = ($agrupadoPorCategoria[$categoria] ?? 0) + 1;
                $agrupadoPorSubtipo[$subtipo] = ($agrupadoPorSubtipo[$subtipo] ?? 0) + 1;
                $agrupadoPorEstado[$estado] = ($agrupadoPorEstado[$estado] ?? 0) + 1;
            }

            return [
                'total' => count($productos),
                'por_categoria' => $agrupadoPorCategoria,
                'por_subtipo' => $agrupadoPorSubtipo,
                'por_estado' => $agrupadoPorEstado,
                'ultimos_registros' => array_slice($productos, 0, 10),
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener portafolio CTeI: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAlertasTablero(array $filtros = []): array
    {
        try {
            $alertasPendientes = $this->alertaRepository->getPendientes();
            $alertasVencidas = $this->alertaRepository->getVencidas();
            $alertasCriticas = $this->alertaRepository->getCriticas();

            $productosProximosVencer = $this->productoRepository->getProximosAVencer(90);
            $productosFueraVentana = $this->productoRepository->getFueraVentana();

            return [
                'alertas' => [
                    'pendientes' => count($alertasPendientes),
                    'vencidas' => count($alertasVencidas),
                    'criticas' => count($alertasCriticas),
                    'ultimas' => array_slice($alertasPendientes, 0, 10),
                ],
                'productos_ventana' => [
                    'proximos_vencer' => count($productosProximosVencer),
                    'fuera_ventana' => count($productosFueraVentana),
                    'ultimos_proximos' => array_slice($productosProximosVencer, 0, 5),
                ],
                'total_alertas' => count($alertasPendientes) + count($alertasVencidas),
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener alertas de tablero: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAportesSemillerosProyectos(int $grupoId): array
    {
        try {
            $grupo = $this->grupoRepository->findById($grupoId);
            
            if (!$grupo) {
                throw new Exception("Grupo no encontrado");
            }

            $semillerosDelGrupo = $grupo['semilleros'] ?? [];
            
            $aportes = [];
            foreach ($semillerosDelGrupo as $semillero) {
                $proyectosSemillero = $this->proyectoRepository->all([
                    'search' => $semillero['nombre'],
                    'activo' => true,
                ])->items();

                $aportes[] = [
                    'semillero' => $semillero,
                    'proyectos_count' => count($proyectosSemillero),
                    'proyectos' => array_slice($proyectosSemillero, 0, 5),
                ];
            }

            return [
                'grupo' => $grupo,
                'total_semilleros' => count($semillerosDelGrupo),
                'total_proyectos' => array_sum(array_column($aportes, 'proyectos_count')),
                'aportes' => $aportes,
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener aportes de semilleros: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMetricasCorteActual(): array
    {
        try {
            $corteActual = $this->corteRepository->getUltimoCorte();
            
            if (!$corteActual) {
                return [
                    'mensaje' => 'No hay cortes históricos disponibles',
                    'corte' => null,
                    'metricas' => [],
                ];
            }

            $metricas = $corteActual['metricas_activos'] ?? [];
            
            $metricasAgrupadas = [];
            foreach ($metricas as $metrica) {
                $tipo = $metrica['tipo_metrica'];
                $metricasAgrupadas[$tipo][] = $metrica;
            }

            return [
                'corte' => [
                    'id' => $corteActual['id'],
                    'nombre' => $corteActual['nombre'],
                    'version' => $corteActual['version'],
                    'fecha_corte' => $corteActual['fecha_corte'],
                    'estado' => $corteActual['estado'],
                ],
                'resumen' => [
                    'total_productos' => $corteActual['total_productos'],
                    'productos_dentro_ventana' => $corteActual['productos_dentro_ventana'],
                    'productos_proximo_vencer' => $corteActual['productos_proximo_vencer'],
                    'productos_fuera_ventana' => $corteActual['productos_fuera_ventana'],
                    'expedientes_incompletos' => $corteActual['expedientes_incompletos'],
                    'porcentaje_dentro_ventana' => $corteActual['porcentaje_dentro_ventana'],
                    'porcentaje_completos' => $corteActual['porcentaje_expedientes_completos'],
                ],
                'metricas' => $metricasAgrupadas,
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener métricas de corte actual: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTendenciasProduccion(int $meses = 12): array
    {
        try {
            $productos = $this->productoRepository->all([
                'activo' => true,
                'fecha_inicio' => now()->subMonths($meses)->toDateString(),
            ])->items();

            $tendencias = [];
            for ($i = 0; $i < $meses; $i++) {
                $fecha = now()->subMonths($i);
                $mes = $fecha->format('Y-m');
                
                $productosMes = array_filter($productos, function ($producto) use ($mes) {
                    return substr($producto['fecha_publicacion'], 0, 7) === $mes;
                });

                $tendencias[] = [
                    'mes' => $mes,
                    'cantidad' => count($productosMes),
                    'categorias' => $this->agruparPorCategoria($productosMes),
                ];
            }

            return [
                'periodo' => "{$meses} meses",
                'tendencias' => array_reverse($tendencias),
            ];
        } catch (Exception $e) {
            Log::error("Error al obtener tendencias de producción: " . $e->getMessage());
            throw $e;
        }
    }

    protected function agruparPorCategoria(array $productos): array
    {
        $categorias = ['GNC' => 0, 'DTI' => 0, 'ASC-DPC' => 0, 'FRH' => 0];
        
        foreach ($productos as $producto) {
            $categoria = $producto['subtipo']['categoria']['tipo_categoria'] ?? 'Sin Categoría';
            if (array_key_exists($categoria, $categorias)) {
                $categorias[$categoria]++;
            }
        }
        
        return $categorias;
    }

    public function compararCortes(int $corteId1, int $corteId2): array
    {
        try {
            $corte1 = $this->corteRepository->findById($corteId1);
            $corte2 = $this->corteRepository->findById($corteId2);
            
            if (!$corte1 || !$corte2) {
                throw new Exception("Uno o ambos cortes no encontrados");
            }

            return [
                'corte1' => [
                    'nombre' => $corte1['nombre'],
                    'version' => $corte1['version'],
                    'fecha' => $corte1['fecha_corte'],
                    'total_productos' => $corte1['total_productos'],
                    'productos_dentro_ventana' => $corte1['productos_dentro_ventana'],
                    'productos_fuera_ventana' => $corte1['productos_fuera_ventana'],
                ],
                'corte2' => [
                    'nombre' => $corte2['nombre'],
                    'version' => $corte2['version'],
                    'fecha' => $corte2['fecha_corte'],
                    'total_productos' => $corte2['total_productos'],
                    'productos_dentro_ventana' => $corte2['productos_dentro_ventana'],
                    'productos_fuera_ventana' => $corte2['productos_fuera_ventana'],
                ],
                'diferencias' => [
                    'cambio_total_productos' => $corte2['total_productos'] - $corte1['total_productos'],
                    'cambio_dentro_ventana' => $corte2['productos_dentro_ventana'] - $corte1['productos_dentro_ventana'],
                    'cambio_fuera_ventana' => $corte2['productos_fuera_ventana'] - $corte1['productos_fuera_ventana'],
                ],
            ];
        } catch (Exception $e) {
            Log::error("Error al comparar cortes: " . $e->getMessage());
            throw $e;
        }
    }
}
