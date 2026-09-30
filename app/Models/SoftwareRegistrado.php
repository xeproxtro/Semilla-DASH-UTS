<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftwareRegistrado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'software_registrado';

    protected $fillable = [
        'producto_id',
        'nombre',
        'version',
        'anio_desarrollo',
        'tipo',
        'titular',
        'licencia',
        'disponibilidad',
        'url_repositorio',
        'url_descarga',
        'descripcion_tecnica',
        'plataforma',
        'lenguaje_programacion',
        'lineas_codigo',
        'tiene_certificacion_innovacion',
        'entidad_certificadora',
        'fecha_certificacion',
        'numero_registro_software',
        'fecha_registro_dnda',
        'nombre_soporte_logico',
        'tipo_soporte_logico',
        'url_soporte_logico',
        'activo',
    ];

    protected $casts = [
        'anio_desarrollo' => 'integer',
        'lineas_codigo' => 'integer',
        'tiene_certificacion_innovacion' => 'boolean',
        'fecha_certificacion' => 'date',
        'fecha_registro_dnda' => 'date',
        'activo' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function fases()
    {
        return $this->hasMany(FaseSoftware::class);
    }

    public function fasesActivas()
    {
        return $this->fases()->where('activo', true);
    }

    public function certificaciones()
    {
        return $this->hasMany(CertificacionInnovacion::class);
    }

    public function certificacionesActivas()
    {
        return $this->certificaciones()->where('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorDisponibilidad($query, $disponibilidad)
    {
        return $query->where('disponibilidad', $disponibilidad);
    }

    public function scopeConCertificacion($query)
    {
        return $query->where('tiene_certificacion_innovacion', true);
    }

    public function scopePorAnio($query, $anio)
    {
        return $query->where('anio_desarrollo', $anio);
    }

    public function getFasesCompletadasAttribute(): int
    {
        return $this->fasesActivas()->where('estado', 'Completado')->count();
    }

    public function getProgresoFasesAttribute(): float
    {
        $totalFases = $this->fasesActivas()->count();
        if ($totalFases === 0) return 0;
        
        return ($this->fases_completadas / $totalFases) * 100;
    }

    public function getFasesPendientesAttribute(): int
    {
        return $this->fasesActivas()->where('estado', '!=', 'Completado')->count();
    }

    public function getEstaCompletoAttribute(): bool
    {
        return $this->fasesActivas()->where('estado', '!=', 'Completado')->count() === 0;
    }

    public function getUltimaCertificacionAttribute(): ?CertificacionInnovacion
    {
        return $this->certificacionesActivas()->latest('fecha_emision')->first();
    }
}
