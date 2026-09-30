<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('fecha_asignacion', 'fecha_expiracion', 'activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
