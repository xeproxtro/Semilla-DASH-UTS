<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampoPersonalizado extends Model
{
    use HasFactory;

    protected $table = 'campos_personalizados';

    protected $fillable = [
        'subtipo_id',
        'nombre_campo',
        'tipo_dato',
        'obligatorio',
        'opciones',
        'descripcion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
        'opciones' => 'array',
    ];

    public function subtipo()
    {
        return $this->belongsTo(SubtipoProducto::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorSubtipo($query, $subtipoId)
    {
        return $query->where('subtipo_id', $subtipoId);
    }

    public function scopePorOrden($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeObligatorios($query)
    {
        return $query->where('obligatorio', true);
    }
}
