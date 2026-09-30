<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'planes_trabajo';

    protected $fillable = [
        'grupo_id',
        'linea_investigacion_id',
        'nombre',
        'version',
        'fecha_inicio',
        'fecha_fin',
        'objetivos',
        'metas',
        'estado',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function lineaInvestigacion()
    {
        return $this->belongsTo(LineaInvestigacion::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }
}
