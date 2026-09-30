<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlertaTablero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alertas_tablero';

    protected $fillable = [
        'corte_id',
        'tipo_alerta',
        'producto_id',
        'grupo_id',
        'semillero_id',
        'titulo',
        'descripcion',
        'prioridad',
        'fecha_alerta',
        'fecha_resolucion',
        'resuelta',
        'asignado_a',
        'acciones_requeridas',
        'activo',
    ];

    protected $casts = [
        'fecha_alerta' => 'date',
        'fecha_resolucion' => 'date',
        'resuelta' => 'boolean',
        'activo' => 'boolean',
    ];

    public function corte()
    {
        return $this->belongsTo(CorteHistorico::class);
    }

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function semillero()
    {
        return $this->belongsTo(Semillero::class);
    }

    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_alerta', $tipo);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopePorCorte($query, $corteId)
    {
        return $query->where('corte_id', $corteId);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }

    public function scopePorSemillero($query, $semilleroId)
    {
        return $query->where('semillero_id', $semilleroId);
    }

    public function scopeResueltas($query)
    {
        return $query->where('resuelta', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('resuelta', false);
    }

    public function scopeCriticas($query)
    {
        return $query->where('prioridad', 'Critica');
    }

    public function scopeAltas($query)
    {
        return $query->where('prioridad', 'Alta');
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_alerta', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_alerta', '>=', $fechaInicio);
    }

    public function scopeVencidas($query)
    {
        return $query->where('fecha_alerta', '<', now())
                    ->where('resuelta', false);
    }

    public function scopeProximasAVencer($query, $dias = 7)
    {
        return $query->whereBetween('fecha_alerta', [now(), now()->addDays($dias)])
                    ->where('resuelta', false);
    }

    public function getDiasParaResolucionAttribute(): ?int
    {
        if ($this->fecha_alerta && !$this->resuelta) {
            return now()->diffInDays($this->fecha_alerta, false);
        }
        return null;
    }

    public function getEstaVencidaAttribute(): bool
    {
        return $this->dias_para_resolucion !== null && $this->dias_para_resolucion < 0;
    }

    public function getEstaUrgenteAttribute(): bool
    {
        return in_array($this->prioridad, ['Alta', 'Critica']) && !$this->resuelta;
    }

    public function getColorPrioridadAttribute(): string
    {
        $colores = [
            'Baja' => 'green',
            'Media' => 'yellow',
            'Alta' => 'orange',
            'Critica' => 'red',
        ];

        return $colores[$this->prioridad] ?? 'gray';
    }
}
