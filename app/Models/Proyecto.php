<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'titulo',
        'objetivo_general',
        'objetivos_especificos',
        'tipo',
        'convocatoria',
        'linea_investigacion_id',
        'fecha_inicio',
        'fecha_fin',
        'fecha_fin_real',
        'estado',
        'presupuesto_total',
        'director',
        'director_id',
        'resumen',
        'palabras_clave',
        'url_externa',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_fin_real' => 'date',
        'presupuesto_total' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function lineaInvestigacion()
    {
        return $this->belongsTo(LineaInvestigacion::class);
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'proyecto_grupo')
            ->withPivot('rol', 'fecha_asociacion', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function gruposActivos()
    {
        return $this->grupos()->where('activo', true);
    }

    public function grupoPrincipal()
    {
        return $this->grupos()->wherePivot('rol', 'Principal')->first();
    }

    public function semilleros()
    {
        return $this->belongsToMany(Semillero::class, 'proyecto_semillero')
            ->withPivot('rol', 'fecha_asociacion', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function semillerosActivos()
    {
        return $this->semilleros()->where('activo', true);
    }

    public function instituciones()
    {
        return $this->belongsToMany(Institucion::class, 'proyecto_institucion')
            ->withPivot('tipo_participacion', 'fecha_asociacion', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function institucionesActivas()
    {
        return $this->instituciones()->where('activo', true);
    }

    public function participantes()
    {
        return $this->hasMany(ParticipanteProyecto::class);
    }

    public function participantesActivos()
    {
        return $this->participantes()->where('activo', true);
    }

    public function fuentesFinanciacion()
    {
        return $this->hasMany(FuenteFinanciacion::class);
    }

    public function fuentesActivas()
    {
        return $this->fuentesFinanciacion()->where('activo', true);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    public function actividadesActivas()
    {
        return $this->actividades()->where('activo', true);
    }

    public function riesgos()
    {
        return $this->hasMany(RiesgoProyecto::class);
    }

    public function riesgosActivos()
    {
        return $this->riesgos()->where('activo', true);
    }

    public function avances()
    {
        return $this->hasMany(AvanceProyecto::class);
    }

    public function decisiones()
    {
        return $this->hasMany(DecisionProyecto::class);
    }

    public function productos()
    {
        return $this->belongsToMany(ProductoCtei::class, 'producto_proyecto')
            ->withPivot('resultado_principal', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorDirector($query, $directorId)
    {
        return $query->where('director_id', $directorId);
    }

    public function scopePorLinea($query, $lineaId)
    {
        return $query->where('linea_investigacion_id', $lineaId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_inicio', '>=', $fechaInicio);
    }

    public function getDuracionDiasAttribute(): int
    {
        if ($this->fecha_fin) {
            return $this->fecha_inicio->diffInDays($this->fecha_fin);
        }
        return 0;
    }

    public function getDuracionRealDiasAttribute(): ?int
    {
        if ($this->fecha_fin_real) {
            return $this->fecha_inicio->diffInDays($this->fecha_fin_real);
        }
        return null;
    }

    public function getPresupuestoEjecutadoAttribute(): float
    {
        return $this->fuentesActivas()->sum('monto');
    }

    public function getPorcentajeEjecucionAttribute(): float
    {
        if ($this->presupuesto_total > 0) {
            return ($this->presupuesto_ejecutado / $this->presupuesto_total) * 100;
        }
        return 0;
    }
}
