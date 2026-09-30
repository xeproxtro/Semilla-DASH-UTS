<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_producto';

    protected $fillable = [
        'version_catalogo_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo_categoria',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function versionCatalogo()
    {
        return $this->belongsTo(VersionCatalogo::class);
    }

    public function subtipos()
    {
        return $this->hasMany(SubtipoProducto::class);
    }

    public function subtiposActivos()
    {
        return $this->subtipos()->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_categoria', $tipo);
    }

    public function scopePorVersion($query, $versionId)
    {
        return $query->where('version_catalogo_id', $versionId);
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }
}
