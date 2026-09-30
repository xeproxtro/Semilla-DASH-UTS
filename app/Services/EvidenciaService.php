<?php

namespace App\Services;

use App\Repositories\EvidenciaRepository;
use App\Repositories\ProductoCteiRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class EvidenciaService
{
    protected EvidenciaRepository $evidenciaRepository;
    protected ProductoCteiRepository $productoRepository;

    public function __construct(
        EvidenciaRepository $evidenciaRepository,
        ProductoCteiRepository $productoRepository
    ) {
        $this->evidenciaRepository = $evidenciaRepository;
        $this->productoRepository = $productoRepository;
    }

    public function getAllEvidencias(array $filters = [])
    {
        return $this->evidenciaRepository->all($filters);
    }

    public function getEvidenciaById(int $id)
    {
        return $this->evidenciaRepository->findById($id);
    }

    public function createEvidencia(array $data)
    {
        try {
            $this->validateEvidenciaData($data);
            
            if (empty($data['producto_id'])) {
                throw new Exception("El producto es obligatorio");
            }

            $producto = $this->productoRepository->findById($data['producto_id']);
            if (!$producto) {
                throw new Exception("El producto especificado no existe");
            }

            if (isset($data['archivo'])) {
                $rutaArchivo = $this->almacenarArchivo($data['archivo'], $data['producto_id']);
                $data['ruta_archivo'] = $rutaArchivo;
                $data['hash_integridad'] = $this->calcularHashArchivo($rutaArchivo);
                unset($data['archivo']);
            }

            $evidencia = $this->evidenciaRepository->create($data);
            
            Log::info("Evidencia creada: {$evidencia->nombre}", ['evidencia_id' => $evidencia->id]);
            return $evidencia;
        } catch (Exception $e) {
            Log::error("Error al crear evidencia: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateEvidencia(int $id, array $data)
    {
        try {
            $this->validateEvidenciaData($data, true);
            
            if (isset($data['archivo'])) {
                $rutaArchivo = $this->almacenarArchivo($data['archivo'], $data['producto_id']);
                $data['ruta_archivo'] = $rutaArchivo;
                $data['hash_integridad'] = $this->calcularHashArchivo($rutaArchivo);
                unset($data['archivo']);
            }

            $evidencia = $this->evidenciaRepository->update($id, $data);
            
            Log::info("Evidencia actualizada: {$evidencia->nombre}", ['evidencia_id' => $id]);
            return $evidencia;
        } catch (Exception $e) {
            Log::error("Error al actualizar evidencia: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteEvidencia(int $id)
    {
        try {
            $evidencia = $this->evidenciaRepository->findById($id);
            
            if ($evidencia && $evidencia->ruta_archivo) {
                $this->eliminarArchivo($evidencia->ruta_archivo);
            }

            $result = $this->evidenciaRepository->delete($id);
            
            Log::info("Evidencia eliminada", ['evidencia_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar evidencia: " . $e->getMessage());
            throw $e;
        }
    }

    public function validarIntegridad(int $id, string $hash): bool
    {
        try {
            return $this->evidenciaRepository->validarIntegridad($id, $hash);
        } catch (Exception $e) {
            Log::error("Error al validar integridad: " . $e->getMessage());
            throw $e;
        }
    }

    public function marcarValidada(int $id): bool
    {
        try {
            return $this->evidenciaRepository->marcarValidada($id);
        } catch (Exception $e) {
            Log::error("Error al marcar evidencia como validada: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByProducto(int $productoId): array
    {
        return $this->evidenciaRepository->getByProducto($productoId);
    }

    public function getArchivosByProducto(int $productoId): array
    {
        return $this->evidenciaRepository->getArchivosByProducto($productoId);
    }

    public function getEnlacesByProducto(int $productoId): array
    {
        return $this->evidenciaRepository->getEnlacesByProducto($productoId);
    }

    protected function almacenarArchivo($archivo, int $productoId): string
    {
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        
        $ruta = "evidencias/producto_{$productoId}/{$nombreArchivo}";
        
        Storage::disk('private')->put($ruta, file_get_contents($archivo));
        
        return Storage::disk('private')->path($ruta);
    }

    protected function eliminarArchivo(string $ruta): void
    {
        if (Storage::disk('private')->exists($ruta)) {
            Storage::disk('private')->delete($ruta);
        }
    }

    protected function calcularHashArchivo(string $ruta): string
    {
        return hash_file('sha256', $ruta);
    }

    protected function validateEvidenciaData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['nombre'])) {
                throw new Exception("El nombre de la evidencia es obligatorio");
            }
            if (empty($data['tipo'])) {
                throw new Exception("El tipo de evidencia es obligatorio");
            }
        }

        if (isset($data['tipo'])) {
            $tiposValidos = ['Archivo', 'Enlace', 'Documento', 'Video', 'Audio', 'Imagen', 'Otro'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new Exception("Tipo de evidencia no válido");
            }
        }

        if (isset($data['tipo']) && $data['tipo'] === 'Archivo' && !isset($data['archivo']) && !isset($data['ruta_archivo'])) {
            throw new Exception("Para tipo Archivo se requiere un archivo");
        }

        if (isset($data['tipo']) && $data['tipo'] === 'Enlace' && !isset($data['url_enlace'])) {
            throw new Exception("Para tipo Enlace se requiere una URL");
        }

        if (isset($data['url_enlace']) && !filter_var($data['url_enlace'], FILTER_VALIDATE_URL)) {
            throw new Exception("La URL del enlace no es válida");
        }

        if (isset($data['nivel_acceso'])) {
            $nivelesValidos = ['Privado', 'Interno', 'Publico'];
            if (!in_array($data['nivel_acceso'], $nivelesValidos)) {
                throw new Exception("Nivel de acceso no válido");
            }
        }
    }
}
