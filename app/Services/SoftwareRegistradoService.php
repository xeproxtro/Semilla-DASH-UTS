<?php

namespace App\Services;

use App\Repositories\SoftwareRegistradoRepository;
use App\Repositories\ProductoCteiRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class SoftwareRegistradoService
{
    protected SoftwareRegistradoRepository $softwareRepository;
    protected ProductoCteiRepository $productoRepository;
    protected UserRepository $userRepository;

    public function __construct(
        SoftwareRegistradoRepository $softwareRepository,
        ProductoCteiRepository $productoRepository,
        UserRepository $userRepository
    ) {
        $this->softwareRepository = $softwareRepository;
        $this->productoRepository = $productoRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllSoftware(array $filters = [])
    {
        return $this->softwareRepository->all($filters);
    }

    public function getSoftwareById(int $id)
    {
        return $this->softwareRepository->findById($id);
    }

    public function createSoftware(array $data)
    {
        try {
            $this->validateSoftwareData($data);
            
            if (empty($data['producto_id'])) {
                throw new Exception("El producto asociado es obligatorio");
            }

            $producto = $this->productoRepository->findById($data['producto_id']);
            if (!$producto) {
                throw new Exception("El producto especificado no existe");
            }

            $software = $this->softwareRepository->create($data);
            
            if (isset($data['fases']) && is_array($data['fases'])) {
                $this->crearFasesSoftware($software->id, $data['fases']);
            }

            Log::info("Software registrado: {$software->nombre}", ['software_id' => $software->id]);
            return $software;
        } catch (Exception $e) {
            Log::error("Error al registrar software: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateSoftware(int $id, array $data)
    {
        try {
            $this->validateSoftwareData($data, true);
            
            $software = $this->softwareRepository->update($id, $data);
            
            if (isset($data['fases']) && is_array($data['fases'])) {
                $this->actualizarFasesSoftware($id, $data['fases']);
            }

            Log::info("Software actualizado: {$software->nombre}", ['software_id' => $id]);
            return $software;
        } catch (Exception $e) {
            Log::error("Error al actualizar software: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteSoftware(int $id)
    {
        try {
            $software = $this->softwareRepository->findById($id);
            
            if ($software && $software->fasesActivas()->count() > 0) {
                throw new Exception("No se puede eliminar el software porque tiene fases activas");
            }

            $result = $this->softwareRepository->delete($id);
            
            Log::info("Software eliminado", ['software_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar software: " . $e->getMessage());
            throw $e;
        }
    }

    public function crearFasesSoftware(int $softwareId, array $fasesData): void
    {
        $fasesObligatorias = ['Analisis', 'Diseño', 'Implementacion', 'Validacion'];
        
        foreach ($fasesObligatorias as $faseNombre) {
            $faseEncontrada = false;
            
            foreach ($fasesData as $faseData) {
                if ($faseData['fase'] === $faseNombre) {
                    $faseEncontrada = true;
                    
                    app(FaseSoftwareRepository::class)->create([
                        'software_id' => $softwareId,
                        'fase' => $faseNombre,
                        'descripcion_fase' => $faseData['descripcion_fase'] ?? '',
                        'fecha_inicio' => $faseData['fecha_inicio'] ?? now(),
                        'fecha_fin' => $faseData['fecha_fin'] ?? null,
                        'estado' => $faseData['estado'] ?? 'Pendiente',
                        'documentacion' => $faseData['documentacion'] ?? null,
                        'evidencias' => $faseData['evidencias'] ?? null,
                        'responsable_id' => $faseData['responsable_id'] ?? null,
                        'observaciones' => $faseData['observaciones'] ?? null,
                        'activo' => true,
                    ]);
                    
                    break;
                }
            }
            
            if (!$faseEncontrada) {
                app(FaseSoftwareRepository::class)->create([
                    'software_id' => $softwareId,
                    'fase' => $faseNombre,
                    'descripcion_fase' => "Fase de {$faseNombre}",
                    'fecha_inicio' => now(),
                    'estado' => 'Pendiente',
                    'activo' => true,
                ]);
            }
        }
    }

    public function actualizarFasesSoftware(int $softwareId, array $fasesData): void
    {
        $faseRepo = app(FaseSoftwareRepository::class);
        
        foreach ($fasesData as $faseData) {
            $faseExistente = $faseRepo->findBySoftwareAndFase($softwareId, $faseData['fase']);
            
            if ($faseExistente) {
                $faseRepo->update($faseExistente->id, $faseData);
            } else {
                $faseRepo->create([
                    'software_id' => $softwareId,
                    ...$faseData,
                    'activo' => true,
                ]);
            }
        }
    }

    public function cambiarEstadoFase(int $faseId, string $nuevoEstado): bool
    {
        try {
            $estadosValidos = ['Pendiente', 'En Progreso', 'Completado'];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                throw new Exception("Estado no válido: {$nuevoEstado}");
            }

            $faseRepo = app(FaseSoftwareRepository::class);
            return $faseRepo->update($faseId, ['estado' => $nuevoEstado])->estado === $nuevoEstado;
        } catch (Exception $e) {
            Log::error("Error al cambiar estado de fase: " . $e->getMessage());
            throw $e;
        }
    }

    public function agregarCertificacion(int $softwareId, array $certificacionData): bool
    {
        try {
            $this->validateCertificacionData($certificacionData);
            
            $certificacionRepo = app(CertificacionInnovacionRepository::class);
            $certificacion = $certificacionRepo->create([
                'software_id' => $softwareId,
                ...$certificacionData,
                'activo' => true,
            ]);
            
            $softwareRepo = app(SoftwareRegistradoRepository::class);
            $softwareRepo->update($softwareId, [
                'tiene_certificacion_innovacion' => true,
                'entidad_certificadora' => $certificacionData['entidad_certificadora'],
                'fecha_certificacion' => $certificacionData['fecha_emision'],
            ]);
            
            Log::info("Certificación agregada al software", ['software_id' => $softwareId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar certificación: " . $e->getMessage());
            throw $e;
        }
    }

    public function verificarCompletitudFases(int $softwareId): array
    {
        try {
            $software = $this->softwareRepository->findById($softwareId);
            
            if (!$software) {
                throw new Exception("Software no encontrado");
            }

            $fases = $software->fasesActivas;
            $fasesObligatorias = ['Analisis', 'Diseño', 'Implementacion', 'Validacion'];
            $resultado = [
                'completo' => true,
                'fases_completadas' => [],
                'fases_pendientes' => [],
                'progreso_total' => 0,
            ];

            foreach ($fasesObligatorias as $faseNombre) {
                $fase = $fases->firstWhere('fase', $faseNombre);
                
                if ($fase && $fase->estado === 'Completado') {
                    $resultado['fases_completadas'][] = $faseNombre;
                } else {
                    $resultado['fases_pendientes'][] = $faseNombre;
                    $resultado['completo'] = false;
                }
            }

            $totalFases = count($fasesObligatorias);
            $completadas = count($resultado['fases_completadas']);
            $resultado['progreso_total'] = ($completadas / $totalFases) * 100;

            return $resultado;
        } catch (Exception $e) {
            Log::error("Error al verificar completitud de fases: " . $e->getMessage());
            throw $e;
        }
    }

    public function getConCertificacion(): array
    {
        return $this->softwareRepository->getConCertificacion();
    }

    public function getPorAnio(int $anio): array
    {
        return $this->softwareRepository->getPorAnio($anio);
    }

    public function getAllActiveSoftware(): array
    {
        return $this->softwareRepository->getAllActive();
    }

    public function deactivateSoftware(int $id): bool
    {
        try {
            return $this->softwareRepository->update($id, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar software: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateSoftware(int $id): bool
    {
        try {
            return $this->softwareRepository->update($id, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar software: " . $e->getMessage());
            throw $e;
        }
    }

    protected function validateSoftwareData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['nombre'])) {
                throw new Exception("El nombre del software es obligatorio");
            }
            if (empty($data['version'])) {
                throw new Exception("La versión del software es obligatoria");
            }
            if (empty($data['anio_desarrollo'])) {
                throw new Exception("El año de desarrollo es obligatorio");
            }
        }

        if (isset($data['anio_desarrollo'])) {
            $anioActual = date('Y');
            if ($data['anio_desarrollo'] < 1900 || $data['anio_desarrollo'] > $anioActual + 1) {
                throw new Exception("Año de desarrollo no válido");
            }
        }

        if (isset($data['url_repositorio']) && !filter_var($data['url_repositorio'], FILTER_VALIDATE_URL)) {
            throw new Exception("El formato del URL del repositorio es inválido");
        }

        if (isset($data['url_descarga']) && !filter_var($data['url_descarga'], FILTER_VALIDATE_URL)) {
            throw new Exception("El formato del URL de descarga es inválido");
        }
    }

    protected function validateCertificacionData(array $data): void
    {
        if (empty($data['entidad_certificadora'])) {
            throw new Exception("La entidad certificadora es obligatoria");
        }
        if (empty($data['fecha_emision'])) {
            throw new Exception("La fecha de emisión es obligatoria");
        }
        if (empty($data['nivel_innovacion'])) {
            throw new Exception("El nivel de innovación es obligatorio");
        }

        $nivelesValidos = ['Bajo', 'Medio', 'Alto', 'Muy Alto'];
        if (!in_array($data['nivel_innovacion'], $nivelesValidos)) {
            throw new Exception("Nivel de innovación no válido");
        }
    }
}
