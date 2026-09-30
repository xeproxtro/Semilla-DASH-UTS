<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorteHistorico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cortes_historicos';

    protected $fillable = [
        'nombre',
        'version',
        'fecha_corte',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'version_catalogo_id',
        'creado_por',
        'descripcion',
        'estado',
        'instituciones_incluidas',
        'grupos_incluidos',
        'semilleros_incluidos',
        'reglas_ventanas',
        'total_productos',
        'productos_dentro_ventana',
        'productos_proximo_vencer',
        'productos_fuera_ventana',
        'expedientes_incompletos',
        'fecha_congelamiento',
        'activo',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'fecha_congelamiento' => 'datetime',
        'instituciones_incluidas' => 'array',
        'grupos_incluidos' => 'array',
        'semilleros_incluidos' => 'array',
        'reglas_ventanas' => 'array',
        'total_productos' => 'integer',
        'productos_dentro_ventana' => 'integer',
        'productos_proximo_vencer' => 'integer',
        'productos_fuera_ventana' => 'integer',
        'expedientes_incompletos' => 'integer',
        'activo' => 'boolean',
    ];

    public function versionCatalogo()
    {
        return $this->belongsTo(VersionCatalogo::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class);
    }

    public function detallesProductos()
    {
        return $this->hasMany(DetalleCorteProducto::class);
    }

    public function detallesActivos()
    {
        return $this->detallesProductos()->where('activo', true);
    }

    public function metricas()
    {
        return $this->hasMany(MetricaCorte::class);
    }

    public function metricasActivas()
    {
        return $this->metricas()->where('activo', true);
    }

    public function alertas()
    {
        return $this->hasMany(AlertaTablero::class);
    }

    public function alertasActivas()
    {
        return $this->alertas()->where('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'Abierto');
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', 'Cerrado');
    }

    public function scopePorVersionCatalogo($query, $versionId)
    {
        return $query->where('version_catalogo_id', $versionId);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_corte', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_corte', '>=', $fechaInicio);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_corte', '>=', now()->subDays($dias));
    }

    public function getPuedeReabrirAttribute(): bool
    {
        return $this->estado === 'Cerrado' || $this->estado === 'Archivado';
    }

    public function getPuedeCerrarAttribute(): bool
    {
        return $this->estado === 'Abierto';
    }

    public function getPuedeArchivarAttribute(): bool
    {
        return $this->estado === 'Cerrado';
    }

    public function getPorcentajeDentroVentanaAttribute(): float
    {
        if ($this->total_productos === 0) return 0;
        return ($this->productos_dentro_ventana / $this->total_productos) * 100;
    }

    public function getPorcentajeExpedientesCompletosAttribute(): float
    {
        if ($this->total_productos === 0) return 0;
        $expedientesCompletos = $this->total_productos - $this->expedientes_incompletos;
        return ($expedientesCompletos / $this->total_productos) * 100;
    }

    public function getDuracionPeriodoDiasAttribute(): int
    {
        if ($this->fecha_inicio_periodo && $this->fecha_fin_periodo) {
            return $this->fecha_inicio_periodo->diffInDays($this->fecha_fin_periodo);
        }
        return 0;
    }

    public function generarNuevaVersion(): string
    {
        $versionNumerica = (int) str_replace('v', '', $this->version);
        $nuevaVersion = 'v' . ($versionNumerica + 1);
        
        return CorteHistorico::create([
            'nombre' => $this->nombre . ' (Reabierto)',
            'version' => $nuevaVersion,
            'fecha_corte' => now(),
            'fecha_inicio_periodo' => $this->fecha_inicio_periodo,
            'fecha_fin_periodo' => $this->fecha_fin_periodo,
            'version_catalogo_id' => $this->version_catalogo_id,
            'creado_por' => $this->creado_por,
            'descripcion' => 'Reapertura del corte ' . $this->version,
            'estado' => 'Abierto',
            'instituciones_incluidas' => $this->instituciones_incluidas,
            'grupos_incluidos' => $this->grupos_incluidos,
            'semilleros_incluidos' => $this->semilleros_incluidos,
            'reglas_ventanas' => $this->reglas_ventanas,
            'activo' => true,
        ])->version;
    }
}
