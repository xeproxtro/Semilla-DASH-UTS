<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VersionCatalogo;
use App\Models\CategoriaProducto;
use App\Models\SubtipoProducto;
use Illuminate\Support\Facades\DB;

class CatalogoMincienciasSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $versionCatalogo = VersionCatalogo::create([
                'nombre' => 'Modelo Minciencias 2024',
                'version' => '2024.1',
                'fecha_vigencia_inicio' => '2024-01-01',
                'fecha_vigencia_fin' => '2028-12-31',
                'descripcion' => 'Taxonomía de productos de CTeI según modelo Minciencias 2024',
                'activo' => true,
            ]);

            $categorias = [
                [
                    'tipo_categoria' => 'GNC',
                    'nombre' => 'Generación de Nuevo Conocimiento',
                    'descripcion' => 'Productos que generan conocimiento nuevo a través de investigación',
                    'subtipos' => [
                        [
                            'nombre' => 'Artículo de investigación',
                            'codigo' => 'A1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Artículo publicado en revista indexada',
                        ],
                        [
                            'nombre' => 'Libro resultado de investigación',
                            'codigo' => 'L1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Libro con ISBN resultado de investigación',
                        ],
                        [
                            'nombre' => 'Capítulo de libro',
                            'codigo' => 'L2',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Capítulo en libro resultado de investigación',
                        ],
                        [
                            'nombre' => 'Patente',
                            'codigo' => 'P1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Patente concedida o solicitud en trámite',
                        ],
                    ],
                ],
                [
                    'tipo_categoria' => 'DTI',
                    'nombre' => 'Desarrollo Tecnológico e Innovación',
                    'descripcion' => 'Productos que desarrollan tecnología e innovación',
                    'subtipos' => [
                        [
                            'nombre' => 'Software',
                            'codigo' => 'S1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Software registrado o con expediente técnico completo',
                        ],
                        [
                            'nombre' => 'Prototipo',
                            'codigo' => 'T1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Prototipo funcional con documentación',
                        ],
                        [
                            'nombre' => 'Diseño industrial',
                            'codigo' => 'D1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Diseño industrial registrado',
                        ],
                        [
                            'nombre' => 'Variedad vegetal',
                            'codigo' => 'V1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Variedad vegetal registrada',
                        ],
                    ],
                ],
                [
                    'tipo_categoria' => 'ASC-DPC',
                    'nombre' => 'Apropiación Social del Conocimiento',
                    'descripcion' => 'Productos de apropiación social y divulgación',
                    'subtipos' => [
                        [
                            'nombre' => 'Evento técnico/científico',
                            'codigo' => 'E1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Organización de evento técnico o científico',
                        ],
                        [
                            'nombre' => 'Divulgación científica',
                            'codigo' => 'DC1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Artículo de divulgación científica en medios',
                        ],
                        [
                            'nombre' => 'Producción audiovisual',
                            'codigo' => 'AV1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Video, podcast u otro contenido audiovisual',
                        ],
                        [
                            'nombre' => 'Documento de trabajo',
                            'codigo' => 'DT1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Documento de trabajo o policy paper',
                        ],
                    ],
                ],
                [
                    'tipo_categoria' => 'FRH',
                    'nombre' => 'Formación de Recurso Humano',
                    'descripcion' => 'Productos de formación de alto nivel',
                    'subtipos' => [
                        [
                            'nombre' => 'Tesis doctoral',
                            'codigo' => 'TD1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Tesis doctoral aprobada y registrada',
                        ],
                        [
                            'nombre' => 'Trabajo de grado maestría',
                            'codigo' => 'TM1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Trabajo de grado de maestría',
                        ],
                        [
                            'nombre' => 'Trabajo de grado pregrado',
                            'codigo' => 'TP1',
                            'ventana_observacion_anios' => 5,
                            'definicion' => 'Trabajo de grado de pregrado distinguido',
                        ],
                        [
                            'nombre' => 'Programa académico nuevo',
                            'codigo' => 'PA1',
                            'ventana_observacion_anios' => 10,
                            'definicion' => 'Programa académico de nuevo registro calificado',
                        ],
                    ],
                ],
            ];

            foreach ($categorias as $categoriaData) {
                $categoria = CategoriaProducto::create([
                    'version_catalogo_id' => $versionCatalogo->id,
                    'tipo_categoria' => $categoriaData['tipo_categoria'],
                    'nombre' => $categoriaData['nombre'],
                    'descripcion' => $categoriaData['descripcion'],
                    'activo' => true,
                ]);

                foreach ($categoriaData['subtipos'] as $subtipoData) {
                    SubtipoProducto::create([
                        'categoria_id' => $categoria->id,
                        'nombre' => $subtipoData['nombre'],
                        'codigo' => $subtipoData['codigo'],
                        'ventana_observacion_anios' => $subtipoData['ventana_observacion_anios'],
                        'ventana_observacion_dias' => $subtipoData['ventana_observacion_anios'] * 365,
                        'definicion' => $subtipoData['definicion'],
                        'activo' => true,
                    ]);
                }
            }

            DB::commit();
            $this->command->info('✅ Catálogo Minciencias 2024 creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error al crear catálogo: ' . $e->getMessage());
            throw $e;
        }
    }
}
