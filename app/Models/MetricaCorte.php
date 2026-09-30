<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricaCorte extends Model
{
    use HasFactory;

    protected $table = 'metricas_corte';

    protected $fillable = [
        'corte_id',
        'tipo_metrica',
        'categoria',
        'subcategoria',
        'valor',
        'valor_decimal',
        'descripcion',
        'desglose',
        'activo',
    ];

    protected $casts = [
        'valor' => 'integer',
        'valor_decimal' => 'decimal:2',
        'activo' => 'boolean',
        'desglose' => 'array',
    ];

    public function corte()
    {
        return $this->belongsTo(CorteHistorico::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCorte($query, $corteId)
    {
        return $query->where('corte_id', $corteId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_metrica', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeNumericas($query)
    {
        return $query->whereNotNull('valor_decimal');
    }

    public function scopeEnteras($query)
    {
        return $query->whereNotNull('valor');
    }

    public function getValorFormateadoAttribute(): string
    {
        if ($this->valor_decimal !== null) {
            return number_format($this->valor_decimal, 2);
        }
        return number_format($this->valor);
    }
}
