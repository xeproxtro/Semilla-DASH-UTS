<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producto_id',
        'subido_por',
        'tipo',
        'nombre',
        'ruta_archivo',
        'url_enlace',
        'version',
        'descripcion',
        'mime_type',
        'tamano_bytes',
        'hash_integridad',
        'algoritmo_hash',
        'nivel_acceso',
        'fecha_documento',
        'registro_soportado',
        'validado',
        'activo',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
        'validado' => 'boolean',
        'activo' => 'boolean',
        'fecha_documento' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoCtei::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopePorNivelAcceso($query, $nivel)
    {
        return $query->where('nivel_acceso', $nivel);
    }

    public function scopeArchivos($query)
    {
        return $query->where('tipo', 'Archivo');
    }

    public function scopeEnlaces($query)
    {
        return $query->where('tipo', 'Enlace');
    }

    public function scopeValidados($query)
    {
        return $query->where('validado', true);
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
        if ($this->ruta_archivo) {
            return pathinfo($this->ruta_archivo, PATHINFO_EXTENSION);
        }
        return '';
    }

    public function getEsArchivoAttribute(): bool
    {
        return $this->tipo === 'Archivo' && !empty($this->ruta_archivo);
    }

    public function getEsEnlaceAttribute(): bool
    {
        return $this->tipo === 'Enlace' && !empty($this->url_enlace);
    }

    public function verificarIntegridad(string $hash): bool
    {
        return hash_equals($this->hash_integridad, $hash);
    }

    public function calcularHash(): string
    {
        if ($this->ruta_archivo && file_exists($this->ruta_archivo)) {
            return hash_file($this->algoritmo_hash, $this->ruta_archivo);
        }
        return '';
    }
}
