<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proyecto_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'actividad_padre_id',
        'fecha_inicio',
        'fecha_fin',
        'fecha_fin_real',
        'estado',
        'porcentaje_avance',
        'responsable_id',
        'presupuesto_asignado',
        'entregables_esperados',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_fin_real' => 'date',
        'porcentaje_avance' => 'integer',
        'presupuesto_asignado' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function actividadPadre()
    {
        return $this->belongsTo(Actividad::class, 'actividad_padre_id');
    }

    public function subactividades()
    {
        return $this->hasMany(Actividad::class, 'actividad_padre_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function avances()
    {
        return $this->hasMany(AvanceProyecto::class);
    }

    public function scopeActivas($query)
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

    public function scopePorResponsable($query, $responsableId)
    {
        return $query->where('responsable_id', $responsableId);
    }

    public function scopePorProyecto($query, $proyectoId)
    {
        return $query->where('proyecto_id', $proyectoId);
    }

    public function scopeRaiz($query)
    {
        return $query->whereNull('actividad_padre_id');
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

    public function getEstaAtrasadaAttribute(): bool
    {
        if ($this->fecha_fin && $this->estado !== 'Completado') {
            return now()->isAfter($this->fecha_fin);
        }
        return false;
    }
}
