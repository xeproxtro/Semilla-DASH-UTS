<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestaRevision extends Model
{
    use HasFactory;

    protected $table = 'respuestas_revision';

    protected $fillable = [
        'revision_id',
        'item_lista_id',
        'respuesta',
        'cumple',
        'observaciones',
        'respondido_por',
        'fecha_respuesta',
        'activo',
    ];

    protected $casts = [
        'cumple' => 'boolean',
        'fecha_respuesta' => 'datetime',
        'activo' => 'boolean',
    ];

    public function revision()
    {
        return $this->belongsTo(RevisionExpediente::class);
    }

    public function itemLista()
    {
        return $this->belongsTo(ItemListaChequeo::class);
    }

    public function respondidoPor()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRevision($query, $revisionId)
    {
        return $query->where('revision_id', $revisionId);
    }

    public function scopePorItem($query, $itemId)
    {
        return $query->where('item_lista_id', $itemId);
    }

    public function scopeCumplen($query)
    {
        return $query->where('cumple', true);
    }

    public function scopeNoCumplen($query)
    {
        return $query->where('cumple', false);
    }
}
