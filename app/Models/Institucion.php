<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'nit',
        'tipo',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'pais',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function vinculaciones()
    {
        return $this->hasMany(Vinculacion::class);
    }

    public function vinculacionesActivas()
    {
        return $this->vinculaciones()->where('vinculacion_actual', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
