<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListaChequeo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'listas_chequeo';

    protected $fillable = [
        'categoria_id',
        'subtipo_id',
        'nombre',
        'descripcion',
        'tipo_lista',
        'obligatoria',
        'activo',
    ];

    protected $casts = [
        'obligatoria' => 'boolean',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class);
    }

    public function subtipo()
    {
        return $this->belongsTo(SubtipoProducto::class);
    }

    public function items()
    {
        return $this->hasMany(ItemListaChequeo::class);
    }

    public function itemsActivos()
    {
        return $this->items()->where('activo', true)->orderBy('orden');
    }

    public function revisiones()
    {
        return $this->hasMany(RevisionExpediente::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_lista', $tipo);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorSubtipo($query, $subtipoId)
    {
        return $query->where('subtipo_id', $subtipoId);
    }

    public function scopeObligatorias($query)
    {
        return $query->where('obligatoria', true);
    }

    public function getItemsObligatoriosAttribute()
    {
        return $this->itemsActivos()->where('obligatorio', true)->count();
    }

    public function getItemsTotalesAttribute()
    {
        return $this->itemsActivos()->count();
    }
}
