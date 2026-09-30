<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemListaChequeo extends Model
{
    use HasFactory;

    protected $table = 'items_lista_chequeo';

    protected $fillable = [
        'lista_chequeo_id',
        'nombre',
        'descripcion',
        'tipo_item',
        'obligatorio',
        'orden',
        'opciones',
        'activo',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
        'opciones' => 'array',
    ];

    public function listaChequeo()
    {
        return $this->belongsTo(ListaChequeo::class);
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaRevision::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorOrden($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeObligatorios($query)
    {
        return $query->where('obligatorio', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_item', $tipo);
    }
}
