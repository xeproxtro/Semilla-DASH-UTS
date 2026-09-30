<?php

namespace App\Services;

use App\Repositories\ProductoCteiRepository;
use App\Repositories\UserRepository;
use App\Repositories\GrupoRepository;
use App\Repositories\SemilleroRepository;
use App\Repositories\ProyectoRepository;
use App\Repositories\InstitucionRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductoCteiService
{
    protected ProductoCteiRepository $productoRepository;
    protected UserRepository $userRepository;
    protected GrupoRepository $grupoRepository;
    protected SemilleroRepository $semilleroRepository;
    protected ProyectoRepository $proyectoRepository;
    protected InstitucionRepository $institucionRepository;

    public function __construct(
        ProductoCteiRepository $productoRepository,
        UserRepository $userRepository,
        GrupoRepository $grupoRepository,
        SemilleroRepository $semilleroRepository,
        ProyectoRepository $proyectoRepository,
        InstitucionRepository $institucionRepository
    ) {
        $this->productoRepository = $productoRepository;
        $this->userRepository = $userRepository;
        $this->grupoRepository = $grupoRepository;
        $this->semilleroRepository = $semilleroRepository;
        $this->proyectoRepository = $proyectoRepository;
        $this->institucionRepository = $institucionRepository;
    }

    public function getAllProductos(array $filters = [])
    {
        return $this->productoRepository->all($filters);
    }

    public function getProductoById(int $id)
    {
        return $this->productoRepository->findById($id);
    }

    public function createProducto(array $data)
    {
        try {
            $this->validateProductoData($data);
            
            if (empty($data['subtipo_id'])) {
                throw new Exception("El subtipo de producto es obligatorio");
            }

            $data['codigo_interno'] = $this->generarCodigoInterno();
            $data['fecha_registro'] = now();

            $producto = $this->productoRepository->create($data);
            
            if (isset($data['autores']) && is_array($data['autores'])) {
                $this->productoRepository->syncAutores($producto->id, $data['autores']);
                $producto->total_autores = count($data['autores']);
                $producto->save();
            }

            if (isset($data['grupos']) && is_array($data['grupos'])) {
                $this->productoRepository->syncGrupos($producto->id, $data['grupos']);
            }

            if (isset($data['semilleros']) && is_array($data['semilleros'])) {
                foreach ($data['semilleros'] as $semilleroData) {
                    $this->productoRepository->addSemillero(
                        $producto->id,
                        $semilleroData['semillero_id'],
                        $semilleroData['semillero_principal'] ?? false
                    );
                }
            }

            if (isset($data['proyectos']) && is_array($data['proyectos'])) {
                foreach ($data['proyectos'] as $proyectoData) {
                    $this->productoRepository->addProyecto(
                        $producto->id,
                        $proyectoData['proyecto_id'],
                        $proyectoData['resultado_principal'] ?? false
                    );
                }
            }

            if (isset($data['instituciones']) && is_array($data['instituciones'])) {
                foreach ($data['instituciones'] as $institucionData) {
                    $this->productoRepository->addInstitucion(
                        $producto->id,
                        $institucionData['institucion_id'],
                        $institucionData['tipo_participacion'] ?? 'Productora',
                        $institucionData['institucion_principal'] ?? false
                    );
                }
            }

            Log::info("Producto CTeI creado: {$producto->titulo}", ['producto_id' => $producto->id]);
            return $producto;
        } catch (Exception $e) {
            Log::error("Error al crear producto CTeI: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateProducto(int $id, array $data)
    {
        try {
            $this->validateProductoData($data, true);
            
            $producto = $this->productoRepository->update($id, $data);
            
            if (isset($data['autores']) && is_array($data['autores'])) {
                $this->productoRepository->syncAutores($id, $data['autores']);
                $producto->total_autores = count($data['autores']);
                $producto->save();
            }

            if (isset($data['grupos']) && is_array($data['grupos'])) {
                $this->productoRepository->syncGrupos($id, $data['grupos']);
            }

            if (isset($data['semilleros']) && is_array($data['semilleros'])) {
                foreach ($data['semilleros'] as $semilleroData) {
                    $this->productoRepository->addSemillero(
                        $id,
                        $semilleroData['semillero_id'],
                        $semilleroData['semillero_principal'] ?? false
                    );
                }
            }

            if (isset($data['proyectos']) && is_array($data['proyectos'])) {
                foreach ($data['proyectos'] as $proyectoData) {
                    $this->productoRepository->addProyecto(
                        $id,
                        $proyectoData['proyecto_id'],
                        $proyectoData['resultado_principal'] ?? false
                    );
                }
            }

            if (isset($data['instituciones']) && is_array($data['instituciones'])) {
                foreach ($data['instituciones'] as $institucionData) {
                    $this->productoRepository->addInstitucion(
                        $id,
                        $institucionData['institucion_id'],
                        $institucionData['tipo_participacion'] ?? 'Productora',
                        $institucionData['institucion_principal'] ?? false
                    );
                }
            }

            Log::info("Producto CTeI actualizado: {$producto->titulo}", ['producto_id' => $id]);
            return $producto;
        } catch (Exception $e) {
            Log::error("Error al actualizar producto CTeI: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteProducto(int $id)
    {
        try {
            $producto = $this->productoRepository->findById($id);
            
            if ($producto && $producto->autoresActivos()->count() > 0) {
                throw new Exception("No se puede eliminar el producto porque tiene autores activos");
            }

            $result = $this->productoRepository->delete($id);
            
            Log::info("Producto CTeI eliminado", ['producto_id' => $id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar producto CTeI: " . $e->getMessage());
            throw $e;
        }
    }

    public function addAutorToProducto(int $productoId, int $userId, int $ordenAutoria = 0, string $rolAutoria = 'Autor', bool $autorCorrespondencia = false)
    {
        try {
            $userRepo = app(UserRepository::class);
            $user = $userRepo->findById($userId);
            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }

            $this->productoRepository->addAutor($productoId, $userId, $ordenAutoria, $rolAutoria, $autorCorrespondencia);
            
            $producto = $this->productoRepository->findById($productoId);
            $producto->total_autores = $producto->autoresActivos()->count();
            $producto->save();
            
            Log::info("Autor agregado al producto", ['producto_id' => $productoId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al agregar autor al producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeAutorFromProducto(int $productoId, int $userId)
    {
        try {
            $this->productoRepository->removeAutor($productoId, $userId);
            
            $producto = $this->productoRepository->findById($productoId);
            $producto->total_autores = $producto->autoresActivos()->count();
            $producto->save();
            
            Log::info("Autor removido del producto", ['producto_id' => $productoId, 'user_id' => $userId]);
            return true;
        } catch (Exception $e) {
            Log::error("Error al remover autor del producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function cambiarEstadoProducto(int $id, string $nuevoEstado): bool
    {
        try {
            $estadosValidos = ['Borrador', 'Enviado', 'Devuelto', 'Revisado', 'Avalado', 'Reportado'];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                throw new Exception("Estado no válido: {$nuevoEstado}");
            }

            return $this->productoRepository->update($id, ['estado' => $nuevoEstado])->estado === $nuevoEstado;
        } catch (Exception $e) {
            Log::error("Error al cambiar estado del producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProximosAVencer(int $dias = 90): array
    {
        return $this->productoRepository->getProximosAVencer($dias);
    }

    public function getFueraVentana(): array
    {
        return $this->productoRepository->getFueraVentana();
    }

    public function getByEstado(string $estado): array
    {
        return $this->productoRepository->getByEstado($estado);
    }

    public function getBySubtipo(int $subtipoId): array
    {
        return $this->productoRepository->getBySubtipo($subtipoId);
    }

    public function getAllActiveProductos(): array
    {
        return $this->productoRepository->getAllActive();
    }

    public function deactivateProducto(int $id): bool
    {
        try {
            return $this->productoRepository->update($id, ['activo' => false])->activo === false;
        } catch (Exception $e) {
            Log::error("Error al desactivar producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function activateProducto(int $id): bool
    {
        try {
            return $this->productoRepository->update($id, ['activo' => true])->activo === true;
        } catch (Exception $e) {
            Log::error("Error al activar producto: " . $e->getMessage());
            throw $e;
        }
    }

    protected function generarCodigoInterno(): string
    {
        $prefijo = 'PROD';
        $fecha = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "{$prefijo}-{$fecha}-{$random}";
    }

    protected function validateProductoData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate) {
            if (empty($data['titulo'])) {
                throw new Exception("El título del producto es obligatorio");
            }
            if (empty($data['fecha_publicacion'])) {
                throw new Exception("La fecha de publicación es obligatoria");
            }
        }

        if (isset($data['doi']) && !$this->validateDOI($data['doi'])) {
            throw new Exception("El formato del DOI es inválido");
        }

        if (isset($data['isbn']) && !$this->validateISBN($data['isbn'])) {
            throw new Exception("El formato del ISBN es inválido");
        }

        if (isset($data['issn']) && !$this->validateISSN($data['issn'])) {
            throw new Exception("El formato del ISSN es inválido");
        }

        if (isset($data['url']) && !filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new Exception("El formato de la URL es inválido");
        }
    }

    protected function validateDOI(string $doi): bool
    {
        return (bool) preg_match('/^10\.\d{4,9}\/[-._;()/:A-Z0-9]+$/i', $doi);
    }

    protected function validateISBN(string $isbn): bool
    {
        $isbn = preg_replace('/[^0-9X]/', '', strtoupper($isbn));
        return (strlen($isbn) === 10 || strlen($isbn) === 13);
    }

    protected function validateISSN(string $issn): bool
    {
        return (bool) preg_match('/^\d{4}-\d{3}[\dX]$/', $issn);
    }
}
