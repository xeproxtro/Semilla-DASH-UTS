<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semillero extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'nombre',
        'coordinador_id',
        'enfoque',
        'descripcion',
        'plan_formativo',
        'email_contacto',
        'categoria',
        'fecha_creacion',
        'activo',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'activo' => 'boolean',
    ];

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function integrantes()
    {
        return $this->belongsToMany(User::class, 'semillero_user')
            ->withPivot('rol', 'fecha_ingreso', 'fecha_retiro', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function integrantesActivos()
    {
        return $this->integrantes()->where('activo', true);
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'semillero_grupo')
            ->withPivot('fecha_articulacion', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function gruposActivos()
    {
        return $this->grupos()->where('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorCoordinador($query, $coordinadorId)
    {
        return $query->where('coordinador_id', $coordinadorId);
    }
}
