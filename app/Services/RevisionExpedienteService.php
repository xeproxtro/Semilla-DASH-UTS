<?php

namespace App\Services;

use App\Repositories\RevisionExpedienteRepository;
use App\Repositories\HistorialEstadoRepository;
use App\Repositories\ProductoCteiRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class RevisionExpedienteService
{
    protected RevisionExpedienteRepository $revisionRepository;
    protected HistorialEstadoRepository $historialRepository;
    protected ProductoCteiRepository $productoRepository;
    protected UserRepository $userRepository;

    public function __construct(
        RevisionExpedienteRepository $revisionRepository,
        HistorialEstadoRepository $historialRepository,
        ProductoCteiRepository $productoRepository,
        UserRepository $userRepository
    ) {
        $this->revisionRepository = $revisionRepository;
        $this->historialRepository = $historialRepository;
        $this->productoRepository = $productoRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllRevisiones(array $filters = [])
    {
        return $this->revisionRepository->all($filters);
    }

    public function getRevisionById(int $id)
    {
        return $this->revisionRepository->findById($id);
    }

    public function createRevision(array $data)
    {
        try {
            $this->validateRevisionData($data);
            
            if (empty($data['producto_id'])) {
                throw new Exception("El producto es obligatorio");
            }

            $producto = $this->productoRepository->findById($data['producto_id']);
            if (!$producto) {
                throw new Exception("El producto especificado no existe");
            }

            $revision = $this->revisionRepository->create($data);
            
            $this->registrarHistorial(
                $data['producto_id'],
                $data['creado_por'] ?? auth()->id(),
                'Crear',
                null,
                'Borrador',
                'Creación de expediente de revisión',
                'Gestor de Investigación'
            );
            
            Log::info("Revisión de expediente creada", ['revision_id' => $revision->id]);
            return $revision;
        } catch (Exception $e) {
            Log::error("Error al crear revisión: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRevision(int $id, array $data)
    {
        try {
            $this->validateRevisionData($data, true);
            
            $revision = $this->revisionRepository->update($id, $data);
            
            Log::info("Revisión de expediente actualizada", ['revision_id' => $id]);
            return $revision;
        } catch (Exception $e) {
            Log::error("Error al actualizar revisión: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteRevision(int $id)
    {
        try {
            $result = $this->revisionRepository->delete($id);
            
            Log::info("Revisión de expediente eliminada", ['revision_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar revisión: " . $e->getMessage());
            throw $e;
        }
    }

    public function enviarRevision(int $id, int $userId, ?string $observaciones = null): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision || !$revision->puede_enviar) {
                throw new Exception("La revisión no puede ser enviada en su estado actual");
            }

            $estadoAnterior = $revision->estado;
            $this->revisionRepository->cambiarEstado($id, 'Enviado', $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Enviar',
                $estadoAnterior,
                'Enviado',
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Revisión enviada", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al enviar revisión: " . $e->getMessage());
            throw $e;
        }
    }

    public function revisarCompletitud(int $id, int $userId, ?string $observaciones = null): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision || !$revision->puede_revisar) {
                throw new Exception("La revisión no puede ser revisada en su estado actual");
            }

            $revision->calcularCompletitud();
            
            $estadoAnterior = $revision->estado;
            $nuevoEstado = $revision->cumple_requisitos ? 'Revisado' : 'Devuelto';
            
            $this->revisionRepository->cambiarEstado($id, $nuevoEstado, $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Revisar',
                $estadoAnterior,
                $nuevoEstado,
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Revisión completitud revisada", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al revisar completitud: " . $e->getMessage());
            throw $e;
        }
    }

    public function aprobarExpediente(int $id, int $userId, ?string $observaciones = null): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision || !$revision->puede_aprobar) {
                throw new Exception("El expediente no puede ser aprobado en su estado actual");
            }

            $estadoAnterior = $revision->estado;
            $this->revisionRepository->cambiarEstado($id, 'Avalado', $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Aprobar',
                $estadoAnterior,
                'Avalado',
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Expediente aprobado", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al aprobar expediente: " . $e->getMessage());
            throw $e;
        }
    }

    public function devolverExpediente(int $id, int $userId, string $observaciones): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision || !$revision->puede_devolver) {
                throw new Exception("El expediente no puede ser devuelto en su estado actual");
            }

            $estadoAnterior = $revision->estado;
            $this->revisionRepository->cambiarEstado($id, 'Devuelto', $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Devolver',
                $estadoAnterior,
                'Devuelto',
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Expediente devuelto", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al devolver expediente: " . $e->getMessage());
            throw $e;
        }
    }

    public function rechazarExpediente(int $id, int $userId, string $observaciones): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision || !$revision->puede_rechazar) {
                throw new Exception("El expediente no puede ser rechazado en su estado actual");
            }

            $estadoAnterior = $revision->estado;
            $this->revisionRepository->cambiarEstado($id, 'Rechazado', $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Rechazar',
                $estadoAnterior,
                'Rechazado',
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Expediente rechazado", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al rechazar expediente: " . $e->getMessage());
            throw $e;
        }
    }

    public function reportarExpediente(int $id, int $userId, ?string $observaciones = null): bool
    {
        try {
            $revision = $this->revisionRepository->findById($id);
            
            if (!$revision) {
                throw new Exception("Revisión no encontrada");
            }

            $estadoAnterior = $revision->estado;
            $this->revisionRepository->cambiarEstado($id, 'Reportado', $observaciones);
            
            $user = $this->userRepository->findById($userId);
            $rolUsuario = $this->obtenerRolUsuario($userId);
            
            $this->registrarHistorial(
                $revision->producto_id,
                $userId,
                'Reportar',
                $estadoAnterior,
                'Reportado',
                $observaciones,
                $rolUsuario
            );
            
            Log::info("Expediente reportado", ['revision_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al reportar expediente: " . $e->getMessage());
            throw $e;
        }
    }

    public function registrarRespuesta(int $revisionId, int $itemId, array $datos): bool
    {
        try {
            $this->validateRespuestaData($datos);
            
            $datos['respondido_por'] = $datos['respondido_por'] ?? auth()->id();
            
            $this->revisionRepository->registrarRespuesta($revisionId, $itemId, $datos);
            
            Log::info("Respuesta registrada", ['revision_id' => $revisionId, 'item_id' => $itemId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al registrar respuesta: " . $e->getMessage());
            throw $e;
        }
    }

    public function getHistorialProducto(int $productoId): array
    {
        return $this->historialRepository->getByProducto($productoId);
    }

    public function getRevisionesPendientes(): array
    {
        return $this->revisionRepository->getPendientes();
    }

    public function getRevisionesCompletadas(): array
    {
        return $this->revisionRepository->getCompletados();
    }

    protected function registrarHistorial(
        int $productoId,
        int $userId,
        string $accion,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $observaciones,
        ?string $rolUsuario
    ): void {
        $this->historialRepository->registrarTransicion(
            $productoId,
            $userId,
            $accion,
            $estadoAnterior,
            $estadoNuevo,
            $observaciones,
            $rolUsuario,
            request()->ip(),
            [
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
            ]
        );
    }

    protected function obtenerRolUsuario(int $userId): ?string
    {
        $user = $this->userRepository->findById($userId);
        if ($user && $user->roles()->count() > 0) {
            return $user->roles()->first()->slug;
        }
        return null;
    }

    protected function validateRevisionData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['producto_id'])) {
                throw new Exception("El producto es obligatorio");
            }
        }

        if (isset($data['estado'])) {
            $estadosValidos = ['Borrador', 'Enviado', 'Devuelto', 'Revisado', 'Avalado', 'Reportado', 'Validado_Externamente', 'Rechazado', 'Anulado'];
            if (!in_array($data['estado'], $estadosValidos)) {
                throw new Exception("Estado no válido");
            }
        }
    }

    protected function validateRespuestaData(array $data): void
    {
        if (empty($data['item_lista_id'])) {
            throw new Exception("El item de lista es obligatorio");
        }

        if (!isset($data['cumple'])) {
            throw new Exception("El campo cumple es obligatorio");
        }
    }
}
