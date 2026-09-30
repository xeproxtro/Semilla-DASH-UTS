<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'nombre',
        'lider_id',
        'gruplac_id',
        'mision',
        'vision',
        'plan_estrategico',
        'categoria',
        'fecha_constitucion',
        'email_contacto',
        'activo',
    ];

    protected $casts = [
        'fecha_constitucion' => 'date',
        'activo' => 'boolean',
    ];

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    public function miembros()
    {
        return $this->belongsToMany(User::class, 'grupo_user')
            ->withPivot('rol', 'fecha_ingreso', 'fecha_retiro', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function miembrosActivos()
    {
        return $this->miembros()->where('activo', true);
    }

    public function lineasInvestigacion()
    {
        return $this->hasMany(LineaInvestigacion::class);
    }

    public function lineasActivas()
    {
        return $this->lineasInvestigacion()->where('activo', true);
    }

    public function planesTrabajo()
    {
        return $this->hasMany(PlanTrabajo::class);
    }

    public function planesActivos()
    {
        return $this->planesTrabajo()->where('activo', true);
    }

    public function semilleros()
    {
        return $this->belongsToMany(Semillero::class, 'semillero_grupo')
            ->withPivot('fecha_articulacion', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function semillerosActivos()
    {
        return $this->semilleros()->where('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorLider($query, $liderId)
    {
        return $query->where('lider_id', $liderId);
    }
}
