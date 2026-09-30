<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estados';

    protected $fillable = [
        'producto_id',
        'realizado_por',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'observaciones',
        'rol_usuario',
        'direccion_ip',
        'metadata',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
        'metadata' => 'array',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function realizadoPor()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('realizado_por', $userId);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorPeriodo($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_accion', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_accion', '>=', $fechaInicio);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha_accion', '>=', now()->subDays($dias));
    }

    public function getDescripcionAccionAttribute(): string
    {
        $acciones = [
            'Crear' => 'Creación del expediente',
            'Enviar' => 'Envío a revisión',
            'Devolver' => 'Devolución con observaciones',
            'Revisar' => 'Revisión de completitud',
            'Aprobar' => 'Aprobación de expediente',
            'Rechazar' => 'Rechazo de expediente',
            'Reportar' => 'Reporte institucional',
            'Validar' => 'Validación externa',
            'Anular' => 'Anulación de expediente',
        ];

        return $acciones[$this->accion] ?? $this->accion;
    }

    public function getResumenCambioAttribute(): string
    {
        if ($this->estado_anterior && $this->estado_nuevo) {
            return "De {$this->estado_anterior} a {$this->estado_nuevo}";
        }
        return "Estado: {$this->estado_nuevo}";
    }
}
