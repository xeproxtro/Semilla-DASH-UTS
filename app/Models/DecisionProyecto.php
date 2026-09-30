<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecisionProyecto extends Model
{
    use HasFactory;

    protected $table = 'decisiones_proyectos';

    protected $fillable = [
        'proyecto_id',
        'titulo',
        'descripcion',
        'tipo',
        'fecha_decision',
        'tomada_por',
        'justificacion',
        'impacto',
        'estado',
        'fecha_implementacion',
        'activo',
    ];

    protected $casts = [
        'fecha_decision' => 'date',
        'fecha_implementacion' => 'date',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function tomadaPor()
    {
        return $this->belongsTo(User::class, 'tomada_por');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_decision', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_decision', '>=', $fechaInicio);
    }

    public function scopeImplementadas($query)
    {
        return $query->where('estado', 'Implementada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }
}
