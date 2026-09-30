<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoSoftware extends Model
{
    use HasFactory;

    protected $table = 'documentos_software';

    protected $fillable = [
        'fase_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_documento',
        'mime_type',
        'tamano_bytes',
        'descripcion',
        'subido_por',
        'fecha_subida',
        'activo',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
        'fecha_subida' => 'datetime',
        'activo' => 'boolean',
    ];

    public function fase()
    {
        return $this->belongsTo(FaseSoftware::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopePorFase($query, $faseId)
    {
        return $query->where('fase_id', $faseId);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('subido_por', $userId);
    }

    public function getTamanoFormateadoAttribute(): string
    {
        $bytes = $this->tamano_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->nombre_archivo, PATHINFO_EXTENSION);
    }
}
