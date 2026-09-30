<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificacionInnovacion extends Model
{
    use HasFactory;

    protected $table = 'certificaciones_innovacion';

    protected $fillable = [
        'software_id',
        'entidad_certificadora',
        'numero_certificado',
        'fecha_emision',
        'fecha_vigencia',
        'nivel_innovacion',
        'descripcion_innovacion',
        'url_certificado',
        'ruta_documento',
        'activo',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vigencia' => 'date',
        'activo' => 'boolean',
    ];

    public function software()
    {
        return $this->belongsTo(SoftwareRegistrado::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEntidad($query, $entidad)
    {
        return $query->where('entidad_certificadora', 'like', "%{$entidad}%");
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel_innovacion', $nivel);
    }

    public function scopeVigentes($query)
    {
        return $query->where('fecha_vigencia', '>=', now())
                    ->orWhereNull('fecha_vigencia');
    }

    public function scopeVencidas($query)
    {
        return $query->where('fecha_vigencia', '<', now());
    }

    public function getEstaVigenteAttribute(): bool
    {
        return $this->fecha_vigencia === null || now()->lte($this->fecha_vigencia);
    }

    public function getDiasParaVencerAttribute(): ?int
    {
        if ($this->fecha_vigencia) {
            return now()->diffInDays($this->fecha_vigencia, false);
        }
        return null;
    }

    public function getEstaPorVencerAttribute(): bool
    {
        $dias = $this->dias_para_vencer;
        return $dias !== null && $dias > 0 && $dias <= 30;
    }
}
