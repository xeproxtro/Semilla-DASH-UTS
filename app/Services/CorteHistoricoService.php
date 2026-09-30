<?php

namespace App\Services;

use App\Repositories\CorteHistoricoRepository;
use App\Repositories\ProductoCteiRepository;
use App\Repositories\GrupoRepository;
use App\Repositories\SemilleroRepository;
use App\Repositories\AlertaTableroRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class CorteHistoricoService
{
    protected CorteHistoricoRepository $corteRepository;
    protected ProductoCteiRepository $productoRepository;
    protected GrupoRepository $grupoRepository;
    protected SemilleroRepository $semilleroRepository;
    protected AlertaTableroRepository $alertaRepository;

    public function __construct(
        CorteHistoricoRepository $corteRepository,
        ProductoCteiRepository $productoRepository,
        GrupoRepository $grupoRepository,
        SemilleroRepository $semilleroRepository,
        AlertaTableroRepository $alertaRepository
    ) {
        $this->corteRepository = $corteRepository;
        $this->productoRepository = $productoRepository;
        $this->grupoRepository = $grupoRepository;
        $this->semilleroRepository = $semilleroRepository;
        $this->alertaRepository = $alertaRepository;
    }

    public function getAllCortes(array $filters = [])
    {
        return $this->corteRepository->all($filters);
    }

    public function getCorteById(int $id)
    {
        return $this->corteRepository->findById($id);
    }

    public function createCorte(array $data)
    {
        try {
            $this->validateCorteData($data);
            
            if (empty($data['version_catalogo_id'])) {
                throw new Exception("La versión del catálogo es obligatoria");
            }

            $data['version'] = $this->generarVersionUnica();
            $data['fecha_corte'] = now();
            
            $corte = $this->corteRepository->create($data);
            
            $this->procesarCorte($corte);
            
            Log::info("Corte histórico creado: {$corte->nombre}", ['corte_id' => $corte->id]);
            return $corte;
        } catch (Exception $e) {
            Log::error("Error al crear corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateCorte(int $id, array $data)
    {
        try {
            $this->validateCorteData($data, true);
            
            $corte = $this->corteRepository->update($id, $data);
            
            if ($corte->estado === 'Abierto') {
                $this->reprocesarCorte($corte);
            }
            
            Log::info("Corte histórico actualizado: {$corte->nombre}", ['corte_id' => $id]);
            return $corte;
        } catch (Exception $e) {
            Log::error("Error al actualizar corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteCorte(int $id)
    {
        try {
            $corte = $this->corteRepository->findById($id);
            
            if ($corte && $corte->estado === 'Cerrado') {
                throw new Exception("No se puede eliminar un corte cerrado");
            }

            $result = $this->corteRepository->delete($id);
            
            Log::info("Corte histórico eliminado", ['corte_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function cerrarCorte(int $id): bool
    {
        try {
            $corte = $this->corteRepository->findById($id);
            
            if (!$corte || !$corte->puede_cerrar) {
                throw new Exception("El corte no puede ser cerrado en su estado actual");
            }

            $resultado = $this->corteRepository->cerrarCorte($id);
            
            if ($resultado) {
                $this->generarAlertasCorte($corte);
            }
            
            Log::info("Corte cerrado", ['corte_id' => $id]);
            return $resultado;
        } catch (Exception $e) {
            Log::error("Error al cerrar corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function reabrirCorte(int $id, int $userId): CorteHistorico
    {
        try {
            $corte = $this->corteRepository->findById($id);
            
            if (!$corte || !$corte->puede_reabrir) {
                throw new Exception("El corte no puede ser reabierto en su estado actual");
            }

            $nuevoCorte = $this->corteRepository->reabrirCorte($id);
            
            Log::info("Corte reabierto", ['corte_id' => $id, 'nueva_version' => $nuevoCorte->version]);
            return $nuevoCorte;
        } catch (Exception $e) {
            Log::error("Error al reabrir corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function archivarCorte(int $id): bool
    {
        try {
            $corte = $this->corteRepository->findById($id);
            
            if (!$corte || !$corte->puede_archivar) {
                throw new Exception("El corte no puede ser archivado en su estado actual");
            }

            $resultado = $this->corteRepository->archivarCorte($id);
            
            Log::info("Corte archivado", ['corte_id' => $id]);
            return $resultado;
        } catch (Exception $e) {
            Log::error("Error al archivar corte: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAbiertos(): array
    {
        return $this->corteRepository->getAbiertos();
    }

    public function getCerrados(): array
    {
        return $this->corteRepository->getCerrados();
    }

    public function getRecientes(int $dias = 30): array
    {
        return $this->corteRepository->getRecientes($dias);
    }

    public function getUltimoCorte(): ?CorteHistorico
    {
        return $this->corteRepository->getUltimoCorte();
    }

    protected function procesarCorte(CorteHistorico $corte): void
    {
        $productos = $this->obtenerProductosEnPeriodo($corte);
        
        $totalProductos = 0;
        $productosDentroVentana = 0;
        $productosProximoVencer = 0;
        $productosFueraVentana = 0;
        $expedientesIncompletos = 0;

        foreach ($productos as $producto) {
            $estadoVentana = $this->calcularEstadoVentana($producto);
            $estadoExpediente = $this->obtenerEstadoExpediente($producto);
            $expedienteCompleto = $this->verificarExpedienteCompleto($producto);
            
            $totalProductos++;
            
            if ($estadoVentana === 'dentro_ventana') {
                $productosDentroVentana++;
            } elseif ($estadoVentana === 'proximo_vencer') {
                $productosProximoVencer++;
            } elseif ($estadoVentana === 'fuera_ventana') {
                $productosFueraVentana++;
            }
            
            if (!$expedienteCompleto) {
                $expedientesIncompletos++;
            }

            $this->crearDetalleCorte($corte, $producto, $estadoVentana, $estadoExpediente, $expedienteCompleto);
        }

        $corte->update([
            'total_productos' => $totalProductos,
            'productos_dentro_ventana' => $productosDentroVentana,
            'productos_proximo_vencer' => $productosProximoVencer,
            'productos_fuera_ventana' => $productosFueraVentana,
            'expedientes_incompletos' => $expedientesIncompletos,
        ]);

        $this->generarMetricasCorte($corte);
    }

    protected function reprocesarCorte(CorteHistorico $corte): void
    {
        $this->procesarCorte($corte);
    }

    protected function obtenerProductosEnPeriodo(CorteHistorico $corte): array
    {
        return $this->productoRepository->all([
            'activo' => true,
            'fecha_inicio' => $corte->fecha_inicio_periodo,
            'fecha_fin' => $corte->fecha_fin_periodo,
        ])->items();
    }

    protected function calcularEstadoVentana($producto): string
    {
        if (!$producto->subtipo || $producto->subtipo->ventana_observacion_anios === 0) {
            return 'no_determinable';
        }

        $ventanaDias = $producto->subtipo->ventana_observacion_dias;
        $fechaLimite = $producto->fecha_publicacion->copy()->addDays($ventanaDias);
        $fechaAdvertencia = $fechaLimite->copy()->subDays(90);
        $now = now();

        if ($now->lte($fechaAdvertencia)) {
            return 'dentro_ventana';
        } elseif ($now->lte($fechaLimite)) {
            return 'proximo_vencer';
        } else {
            return 'fuera_ventana';
        }
    }

    protected function obtenerEstadoExpediente($producto): ?string
    {
        $revision = $producto->revisiones()->where('activo', true)->latest()->first();
        return $revision ? $revision->estado : null;
    }

    protected function verificarExpedienteCompleto($producto): bool
    {
        $revision = $producto->revisiones()->where('activo', true)->latest()->first();
        return $revision && $revision->cumple_requisitos;
    }

    protected function crearDetalleCorte(
        CorteHistorico $corte,
        $producto,
        string $estadoVentana,
        ?string $estadoExpediente,
        bool $expedienteCompleto
    ): void {
        $diasVentana = null;
        if ($producto->subtipo && $producto->subtipo->ventana_observacion_anios > 0) {
            $diasVentana = $producto->subtipo->ventana_observacion_dias;
        }

        $esElegible = $estadoVentana !== 'fuera_ventana' && $expedienteCompleto;

        $corte->detallesProductos()->create([
            'producto_id' => $producto->id,
            'subtipo_id' => $producto->subtipo_id,
            'estado_ventana' => $estadoVentana,
            'estado_expediente' => $estadoExpediente,
            'dias_ventana' => $diasVentana,
            'es_elegible' => $esElegible,
            'expediente_completo' => $expedienteCompleto,
            'metadatos_congelados' => [
                'titulo' => $producto->titulo,
                'fecha_publicacion' => $producto->fecha_publicacion->toDateString(),
                'estado' => $producto->estado,
                'categoria' => $producto->categoria_principal,
            ],
            'activo' => true,
        ]);
    }

    protected function generarMetricasCorte(CorteHistorico $corte): void
    {
        $detalles = $corte->detallesActivos;
        
        $metricas = [
            'Total Productos' => $detalles->count(),
            'Elegibles' => $detalles->where('es_elegible', true)->count(),
            'Dentro Ventana' => $detalles->where('estado_ventana', 'dentro_ventana')->count(),
            'Próximo Vencer' => $detalles->where('estado_ventana', 'proximo_vencer')->count(),
            'Fuera Ventana' => $detalles->where('estado_ventana', 'fuera_ventana')->count(),
            'Expedientes Completos' => $detalles->where('expediente_completo', true)->count(),
            'Expedientes Incompletos' => $detalles->where('expediente_completo', false)->count(),
        ];

        foreach ($metricas as $tipo => $valor) {
            $corte->metricas()->create([
                'tipo_metrica' => 'Resumen',
                'categoria' => 'General',
                'valor' => $valor,
                'descripcion' => $tipo,
                'activo' => true,
            ]);
        }

        $this->generarMetricasPorCategoria($corte, $detalles);
    }

    protected function generarMetricasPorCategoria(CorteHistorico $corte, $detalles): void
    {
        $categorias = ['GNC', 'DTI', 'ASC-DPC', 'FRH'];
        
        foreach ($categorias as $categoria) {
            $detallesCategoria = $detalles->filter(function ($detalle) use ($categoria) {
                return $detalle->producto->categoria_principal === $categoria;
            });

            $corte->metricas()->create([
                'tipo_metrica' => 'Por Categoría',
                'categoria' => $categoria,
                'valor' => $detallesCategoria->count(),
                'valor_decimal' => $corte->total_productos > 0 ? ($detallesCategoria->count() / $corte->total_productos) * 100 : 0,
                'descripcion' => "Productos {$categoria}",
                'activo' => true,
            ]);
        }
    }

    protected function generarAlertasCorte(CorteHistorico $corte): void
    {
        $detalles = $corte->detallesActivos;
        
        foreach ($detalles as $detalle) {
            if ($detalle->requiere_accion) {
                $tipoAlerta = $detalle->estado_ventana === 'proximo_vencer' ? 'Producto_Vencer' : 'Expediente_Incompleto';
                $prioridad = $detalle->estado_ventana === 'fuera_ventana' ? 'Alta' : 'Media';
                
                $this->alertaRepository->create([
                    'corte_id' => $corte->id,
                    'tipo_alerta' => $tipoAlerta,
                    'producto_id' => $detalle->producto_id,
                    'titulo' => $this->generarTituloAlerta($detalle),
                    'descripcion' => $this->generarDescripcionAlerta($detalle),
                    'prioridad' => $prioridad,
                    'fecha_alerta' => now(),
                    'acciones_requeridas' => $this->generarAccionesRequeridas($detalle),
                    'activo' => true,
                ]);
            }
        }
    }

    protected function generarTituloAlerta($detalle): string
    {
        if ($detalle->estado_ventana === 'proximo_vencer') {
            return "Producto próximo a vencer: {$detalle->producto->titulo}";
        } elseif ($detalle->estado_ventana === 'fuera_ventana') {
            return "Producto fuera de ventana: {$detalle->producto->titulo}";
        } elseif (!$detalle->expediente_completo) {
            return "Expediente incompleto: {$detalle->producto->titulo}";
        }
        return "Alerta producto: {$detalle->producto->titulo}";
    }

    protected function generarDescripcionAlerta($detalle): string
    {
        $descripcion = "Estado ventana: {$detalle->descripcion_estado_ventana}";
        
        if (!$detalle->expediente_completo) {
            $descripcion .= ". Expediente requiere atención";
        }
        
        return $descripcion;
    }

    protected function generarAccionesRequeridas($detalle): string
    {
        $acciones = [];
        
        if ($detalle->estado_ventana === 'proximo_vencer') {
            $actions[] = "Actualizar o reproducir producto antes de la fecha límite";
        } elseif ($detalle->estado_ventana === 'fuera_ventana') {
            $actions[] = "Evaluar reproducibilidad del producto";
        }
        
        if (!$detalle->expediente_completo) {
            $actions[] = "Completar expediente y enviar a revisión";
        }
        
        return implode('. ', $actions);
    }

    protected function generarVersionUnica(): string
    {
        $ultimoCorte = $this->corteRepository->getUltimoCorte();
        
        if ($ultimoCorte) {
            $versionNumerica = (int) str_replace('v', '', $ultimoCorte->version);
            return 'v' . ($versionNumerica + 1);
        }
        
        return 'v1';
    }

    protected function validateCorteData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['nombre'])) {
                throw new Exception("El nombre del corte es obligatorio");
            }
            if (empty($data['fecha_inicio_periodo'])) {
                throw new Exception("La fecha inicio del período es obligatoria");
            }
            if (empty($data['fecha_fin_periodo'])) {
                throw new Exception("La fecha fin del período es obligatoria");
            }
        }

        if (isset($data['fecha_inicio_periodo']) && isset($data['fecha_fin_periodo'])) {
            if (strtotime($data['fecha_fin_periodo']) < strtotime($data['fecha_inicio_periodo'])) {
                throw new Exception("La fecha fin no puede ser anterior a la fecha inicio");
            }
        }

        if (isset($data['estado'])) {
            $estadosValidos = ['Abierto', 'Cerrado', 'Archivado'];
            if (!in_array($data['estado'], $estadosValidos)) {
                throw new Exception("Estado no válido");
            }
        }
    }
}
