<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoCtei extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos_ctei';

    protected $fillable = [
        'codigo_interno',
        'subtipo_id',
        'titulo',
        'descripcion',
        'fecha_publicacion',
        'fecha_registro',
        'estado',
        'doi',
        'isbn',
        'issn',
        'patente',
        'registro',
        'handle',
        'url',
        'palabras_clave',
        'idioma',
        'ciudad',
        'pais',
        'total_autores',
        'presupuesto_inversion',
        'moneda',
        'visible_publicamente',
        'activo',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'fecha_registro' => 'date',
        'total_autores' => 'integer',
        'presupuesto_inversion' => 'decimal:2',
        'visible_publicamente' => 'boolean',
        'activo' => 'boolean',
    ];

    public function subtipo()
    {
        return $this->belongsTo(SubtipoProducto::class);
    }

    public function autores()
    {
        return $this->belongsToMany(User::class, 'producto_autor')
            ->withPivot('orden_autoria', 'rol_autoria', 'autor_correspondencia', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true)
            ->orderBy('pivot_orden_autoria');
    }

    public function autoresActivos()
    {
        return $this->autores()->where('activo', true);
    }

    public function autorCorrespondencia()
    {
        return $this->autores()->wherePivot('autor_correspondencia', true)->first();
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'producto_grupo')
            ->withPivot('grupo_principal', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function gruposActivos()
    {
        return $this->grupos()->where('activo', true);
    }

    public function grupoPrincipal()
    {
        return $this->grupos()->wherePivot('grupo_principal', true)->first();
    }

    public function semilleros()
    {
        return $this->belongsToMany(Semillero::class, 'producto_semillero')
            ->withPivot('semillero_principal', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function semillerosActivos()
    {
        return $this->semilleros()->where('activo', true);
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'producto_proyecto')
            ->withPivot('resultado_principal', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function proyectosActivos()
    {
        return $this->proyectos()->where('activo', true);
    }

    public function instituciones()
    {
        return $this->belongsToMany(Institucion::class, 'producto_institucion')
            ->withPivot('tipo_participacion', 'institucion_principal', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function institucionesActivas()
    {
        return $this->instituciones()->where('activo', true);
    }

    public function institucionPrincipal()
    {
        return $this->instituciones()->wherePivot('institucion_principal', true)->first();
    }

    public function financiacion()
    {
        return $this->hasMany(ProductoFinanciacion::class);
    }

    public function financiacionActiva()
    {
        return $this->financiacion()->where('activo', true);
    }

    public function software()
    {
        return $this->hasOne(SoftwareRegistrado::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorSubtipo($query, $subtipoId)
    {
        return $query->where('subtipo_id', $subtipoId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_publicacion', [$fechaInicio, $fechaFin]);
        }
        return $query->where('fecha_publicacion', '>=', $fechaInicio);
    }

    public function scopeVisiblesPublicamente($query)
    {
        return $query->where('visible_publicamente', true);
    }

    public function scopeConIdentificador($query, $tipo, $valor)
    {
        return $query->where($tipo, $valor);
    }

    public function scopePorAutor($query, $userId)
    {
        return $query->whereHas('autores', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopePorGrupo($query, $grupoId)
    {
        return $query->whereHas('grupos', function ($q) use ($grupoId) {
            $q->where('grupo_id', $grupoId);
        });
    }

    public function scopePorSemillero($query, $semilleroId)
    {
        return $query->whereHas('semilleros', function ($q) use ($semilleroId) {
            $q->where('semillero_id', $semilleroId);
        });
    }

    public function scopePorProyecto($query, $proyectoId)
    {
        return $query->whereHas('proyectos', function ($q) use ($proyectoId) {
            $q->where('proyecto_id', $proyectoId);
        });
    }

    public function getEsSoftwareAttribute(): bool
    {
        return $this->software()->exists();
    }

    public function getVentanaObservacionAttribute(): ?int
    {
        return $this->subtipo->ventana_observacion_dias ?? null;
    }

    public function getEstadoVentanaAttribute(): string
    {
        if (!$this->subtipo) {
            return 'no_determinable';
        }

        $ventanaDias = $this->subtipo->ventana_observacion_dias;
        $fechaLimite = $this->fecha_publicacion->copy()->addDays($ventanaDias);
        $fechaAdvertencia = $fechaLimite->copy()->subDays(90);
        $now = now();

        if ($now->lte($fechaAdvertencia)) {
            return 'dentro_ventana';
        } elseif ($now->lte($fechaLimite)) {
            return 'proximo_vencer';
        } else {
            return 'fuera_ventana';
        }
    }

    public function getFinanciacionTotalAttribute(): float
    {
        return $this->financiacionActiva()->sum('monto');
    }

    public function getCategoriaPrincipalAttribute(): ?string
    {
        return $this->subtipo->categoria->tipo_categoria ?? null;
    }
}
