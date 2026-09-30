<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionCatalogo extends Model
{
    use HasFactory;

    protected $table = 'versiones_catalogo';

    protected $fillable = [
        'nombre',
        'version',
        'descripcion',
        'fecha_vigencia_inicio',
        'fecha_vigencia_fin',
        'vigente',
        'fuente',
    ];

    protected $casts = [
        'fecha_vigencia_inicio' => 'date',
        'fecha_vigencia_fin' => 'date',
        'vigente' => 'boolean',
    ];

    public function categorias()
    {
        return $this->hasMany(CategoriaProducto::class);
    }

    public function categoriasActivas()
    {
        return $this->categorias()->where('activo', true);
    }

    public function scopeVigentes($query)
    {
        return $query->where('vigente', true);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->where('fecha_vigencia_inicio', '<=', $fecha)
                    ->where(function ($q) use ($fecha) {
                        $q->whereNull('fecha_vigencia_fin')
                          ->orWhere('fecha_vigencia_fin', '>=', $fecha);
                    });
    }

    public function getEstaVigenteAttribute(): bool
    {
        $now = now();
        return $this->vigente && 
               $now->gte($this->fecha_vigencia_inicio) && 
               (!$this->fecha_vigencia_fin || $now->lte($this->fecha_vigencia_fin));
    }
}
