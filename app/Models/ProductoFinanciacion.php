<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoFinanciacion extends Model
{
    use HasFactory;

    protected $table = 'producto_financiacion';

    protected $fillable = [
        'producto_id',
        'fuente',
        'tipo',
        'monto',
        'moneda',
        'contrato_convenio',
        'activo',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorFuente($query, $fuente)
    {
        return $query->where('fuente', 'like', "%{$fuente}%");
    }
}
