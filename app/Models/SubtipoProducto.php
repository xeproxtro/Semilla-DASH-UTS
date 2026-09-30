<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubtipoProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subtipos_producto';

    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'definicion',
        'ventana_observacion_anios',
        'ventana_observacion_meses',
        'requiere_validacion',
        'activo',
    ];

    protected $casts = [
        'ventana_observacion_anios' => 'integer',
        'ventana_observacion_meses' => 'integer',
        'requiere_validacion' => 'boolean',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class);
    }

    public function camposPersonalizados()
    {
        return $this->hasMany(CampoPersonalizado::class);
    }

    public function camposActivos()
    {
        return $this->camposPersonalizados()->where('activo', true);
    }

    public function productos()
    {
        return $this->hasMany(ProductoCtei::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    public function scopeRequierenValidacion($query)
    {
        return $query->where('requiere_validacion', true);
    }

    public function getVentanaObservacionMesesTotalesAttribute(): int
    {
        return ($this->ventana_observacion_anios * 12) + $this->ventana_observacion_meses;
    }

    public function getVentanaObservacionDiasAttribute(): int
    {
        return $this->ventana_observacion_meses_totales * 30;
    }
}
