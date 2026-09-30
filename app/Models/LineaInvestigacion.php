<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineaInvestigacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lineas_investigacion';

    protected $fillable = [
        'grupo_id',
        'nombre',
        'descripcion',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function planesTrabajo()
    {
        return $this->hasMany(PlanTrabajo::class);
    }

    public function planesActivos()
    {
        return $this->planesTrabajo()->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }
}
