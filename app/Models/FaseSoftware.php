<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaseSoftware extends Model
{
    use HasFactory;

    protected $table = 'fases_software';

    protected $fillable = [
        'software_id',
        'fase',
        'descripcion_fase',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'documentacion',
        'evidencias',
        'responsable_id',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function software()
    {
        return $this->belongsTo(SoftwareRegistrado::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoSoftware::class);
    }

    public function documentosActivos()
    {
        return $this->documentos()->where('activo', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorFase($query, $fase)
    {
        return $query->where('fase', $fase);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorSoftware($query, $softwareId)
    {
        return $query->where('software_id', $softwareId);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', '!=', 'Completado');
    }

    public function getDuracionDiasAttribute(): ?int
    {
        if ($this->fecha_fin) {
            return $this->fecha_inicio->diffInDays($this->fecha_fin);
        }
        return null;
    }

    public function getEstaAtrasadaAttribute(): bool
    {
        if ($this->fecha_fin && $this->estado !== 'Completado') {
            return now()->isAfter($this->fecha_fin);
        }
        return false;
    }

    public function getDocumentosRequeridosAttribute(): bool
    {
        return $this->documentosActivos()->count() > 0;
    }
}
