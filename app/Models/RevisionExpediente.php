<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevisionExpediente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'revisiones_expedientes';

    protected $fillable = [
        'producto_id',
        'lista_chequeo_id',
        'estado',
        'fecha_estado',
        'observaciones',
        'porcentaje_completitud',
        'cumple_requisitos',
        'activo',
    ];

    protected $casts = [
        'fecha_estado' => 'date',
        'porcentaje_completitud' => 'integer',
        'cumple_requisitos' => 'boolean',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function listaChequeo()
    {
        return $this->belongsTo(ListaChequeo::class);
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaRevision::class);
    }

    public function respuestasActivas()
    {
        return $this->respuestas()->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorLista($query, $listaId)
    {
        return $query->where('lista_chequeo_id', $listaId);
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['Borrador', 'Enviado', 'Devuelto']);
    }

    public function scopeCompletados($query)
    {
        return $query->whereIn('estado', ['Revisado', 'Avalado', 'Reportado', 'Validado_Externamente']);
    }

    public function scopeRechazados($query)
    {
        return $query->whereIn('estado', ['Rechazado', 'Anulado']);
    }

    public function getPuedeEnviarAttribute(): bool
    {
        return in_array($this->estado, ['Borrador', 'Devuelto']);
    }

    public function getPuedeRevisarAttribute(): bool
    {
        return $this->estado === 'Enviado';
    }

    public function getPuedeAprobarAttribute(): bool
    {
        return $this->estado === 'Revisado' && $this->cumple_requisitos;
    }

    public function getPuedeDevolverAttribute(): bool
    {
        return in_array($this->estado, ['Enviado', 'Revisado']);
    }

    public function getPuedeRechazarAttribute(): bool
    {
        return in_array($this->estado, ['Enviado', 'Revisado', 'Avalado']);
    }

    public function getPorcentajeCompletitudCalculadoAttribute(): int
    {
        if (!$this->listaChequeo) {
            return $this->porcentaje_completitud;
        }

        $totalItems = $this->listaChequeo->itemsActivos()->count();
        if ($totalItems === 0) return 100;

        $itemsRespondidos = $this->respuestasActivas()->count();
        return ($itemsRespondidos / $totalItems) * 100;
    }

    public function calcularCompletitud(): void
    {
        $this->porcentaje_completitud = $this->porcentaje_completitud_calculado;
        $this->cumple_requisitos = $this->porcentaje_completitud >= 100;
        $this->save();
    }
}
