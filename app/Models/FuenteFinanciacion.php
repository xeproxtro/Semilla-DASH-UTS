<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuenteFinanciacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id',
        'fuente',
        'tipo',
        'monto',
        'moneda',
        'fecha_asignacion',
        'fecha_finalizacion',
        'contrato_convenio',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_asignacion' => 'date',
        'fecha_finalizacion' => 'date',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
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
