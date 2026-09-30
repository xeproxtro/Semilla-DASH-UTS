<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'tipo_documento',
        'numero_documento',
        'password',
        'orcid_id',
        'cvlac_id',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acceso' => 'datetime',
        'activo' => 'boolean',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('fecha_asignacion', 'fecha_expiracion', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function vinculaciones()
    {
        return $this->hasMany(Vinculacion::class);
    }

    public function vinculacionActual()
    {
        return $this->hasOne(Vinculacion::class)
            ->where('vinculacion_actual', true);
    }

    public function gruposComoLider()
    {
        return $this->hasMany(Grupo::class, 'lider_id');
    }

    public function gruposComoMiembro()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_user')
            ->withPivot('rol', 'fecha_ingreso', 'fecha_retiro', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function gruposActivos()
    {
        return $this->gruposComoMiembro()->where('activo', true);
    }

    public function semillerosComoCoordinador()
    {
        return $this->hasMany(Semillero::class, 'coordinador_id');
    }

    public function semillerosComoIntegrante()
    {
        return $this->belongsToMany(Semillero::class, 'semillero_user')
            ->withPivot('rol', 'fecha_ingreso', 'fecha_retiro', 'activo', 'observaciones')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function semillerosActivos()
    {
        return $this->semillerosComoIntegrante()->where('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConRol($query, $roleSlug)
    {
        return $query->whereHas('roles', function ($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }
}
