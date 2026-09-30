<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiesgoProyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'riesgos_proyectos';

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'descripcion',
        'categoria',
        'probabilidad',
        'impacto',
        'estrategia_mitigacion',
        'plan_contingencia',
        'responsable_id',
        'estado',
        'fecha_identificacion',
        'fecha_resolucion',
        'activo',
    ];

    protected $casts = [
        'fecha_identificacion' => 'date',
        'fecha_resolucion' => 'date',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorProbabilidad($query, $probabilidad)
    {
        return $query->where('probabilidad', $probabilidad);
    }

    public function scopePorImpacto($query, $impacto)
    {
        return $query->where('impacto', $impacto);
    }

    public function scopeCriticos($query)
    {
        return $query->where('probabilidad', 'Alta')
                    ->where('impacto', 'Alto');
    }

    public function getNivelRiesgoAttribute(): string
    {
        $matrix = [
            'Baja' => ['Bajo' => 'Bajo', 'Medio' => 'Bajo', 'Alto' => 'Medio'],
            'Media' => ['Bajo' => 'Bajo', 'Medio' => 'Medio', 'Alto' => 'Alto'],
            'Alta' => ['Bajo' => 'Medio', 'Medio' => 'Alto', 'Alto' => 'Critico'],
        ];
        
        return $matrix[$this->probabilidad][$this->impacto] ?? 'Desconocido';
    }
}
