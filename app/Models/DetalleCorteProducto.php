<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCorteProducto extends Model
{
    use HasFactory;

    protected $table = 'detalle_corte_producto';

    protected $fillable = [
        'corte_id',
        'producto_id',
        'subtipo_id',
        'estado_ventana',
        'estado_expediente',
        'dias_ventana',
        'es_elegible',
        'expediente_completo',
        'observaciones',
        'metadatos_congelados',
        'activo',
    ];

    protected $casts = [
        'dias_ventana' => 'integer',
        'es_elegible' => 'boolean',
        'expediente_completo' => 'boolean',
        'activo' => 'boolean',
        'metadatos_congelados' => 'array',
    ];

    public function corte()
    {
        return $this->belongsTo(CorteHistorico::class);
    }

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function subtipo()
    {
        return $this->belongsTo(SubtipoProducto::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCorte($query, $corteId)
    {
        return $query->where('corte_id', $corteId);
    }

    public function scopeElegibles($query)
    {
        return $query->where('es_elegible', true);
    }

    public function scopeNoElegibles($query)
    {
        return $query->where('es_elegible', false);
    }

    public function scopeExpedientesCompletos($query)
    {
        return $query->where('expediente_completo', true);
    }

    public function scopeExpedientesIncompletos($query)
    {
        return $query->where('expediente_completo', false);
    }

    public function scopePorEstadoVentana($query, $estado)
    {
        return $query->where('estado_ventana', $estado);
    }

    public function scopePorEstadoExpediente($query, $estado)
    {
        return $query->where('estado_expediente', $estado);
    }

    public function scopeDentroVentana($query)
    {
        return $query->where('estado_ventana', 'dentro_ventana');
    }

    public function scopeProximoVencer($query)
    {
        return $query->where('estado_ventana', 'proximo_vencer');
    }

    public function scopeFueraVentana($query)
    {
        return $query->where('estado_ventana', 'fuera_ventana');
    }

    public function getDescripcionEstadoVentanaAttribute(): string
    {
        $estados = [
            'dentro_ventana' => 'Dentro de ventana de observación',
            'proximo_vencer' => 'Próximo a vencer',
            'fuera_ventana' => 'Fuera de ventana de observación',
            'no_determinable' => 'No determinable',
        ];

        return $estados[$this->estado_ventana] ?? $this->estado_ventana;
    }

    public function getRequiereAccionAttribute(): bool
    {
        return $this->estado_ventana === 'proximo_vencer' || 
               $this->estado_ventana === 'fuera_ventana' ||
               !$this->expediente_completo;
    }
}
