<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vinculacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'institucion_id',
        'cargo',
        'tipo_vinculacion',
        'fecha_inicio',
        'fecha_fin',
        'vinculacion_actual',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'vinculacion_actual' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('vinculacion_actual', true);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorInstitucion($query, $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }
}
