<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvanceProyecto extends Model
{
    use HasFactory;

    protected $table = 'avances_proyectos';

    protected $fillable = [
        'proyecto_id',
        'actividad_id',
        'fecha_reporte',
        'descripcion_avance',
        'porcentaje_cumplimiento',
        'logros',
        'dificultades',
        'siguientes_pasos',
        'reportado_por',
        'tipo_reporte',
        'activo',
    ];

    protected $casts = [
        'fecha_reporte' => 'date',
        'porcentaje_cumplimiento' => 'integer',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function reportadoPor()
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_reporte', $tipo);
    }

    public function scopePorProyecto($query, $proyectoId)
    {
        return $query->where('proyecto_id', $proyectoId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_reporte', '>=', $fechaInicio);
    }
}
