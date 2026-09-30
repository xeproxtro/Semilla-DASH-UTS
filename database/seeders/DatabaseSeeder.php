<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Institucion;
use App\Models\Vinculacion;
use App\Models\Grupo;
use App\Models\Semillero;
use App\Models\LineaInvestigacion;
use App\Models\PlanTrabajo;
use App\Models\Proyecto;
use App\Models\ParticipanteProyecto;
use App\Models\Actividad;
use App\Models\VersionCatalogo;
use App\Models\CategoriaProducto;
use App\Models\SubtipoProducto;
use App\Models\ProductoCtei;
use App\Models\SoftwareRegistrado;
use App\Models\FaseSoftware;
use App\Models\DocumentoSoftware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $this->call(RoleSeeder::class);
            $this->call(CatalogoMincienciasSeeder::class);

            $adminRole = Role::where('slug', 'administrador')->first();
            $liderRole = Role::where('slug', 'lider_grupo')->first();
            $coordinadorRole = Role::where('slug', 'coordinador_semillero')->first();
            $directorRole = Role::where('slug', 'director_proyecto')->first();

            $adminPassword = env('ADMIN_PASSWORD', 'UTS@Admin2024!');
            $liderPassword = env('LIDER_PASSWORD', 'UTS@Lider2024!');
            $coordinadorPassword = env('COORDINADOR_PASSWORD', 'UTS@Coord2024!');
            $directorPassword = env('DIRECTOR_PASSWORD', 'UTS@Director2024!');

            $adminUser = User::create([
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'email' => env('ADMIN_EMAIL', 'admin@uts.edu.co'),
                'password' => Hash::make($adminPassword),
                'orcid' => null,
                'cvlac' => null,
                'activo' => true,
            ]);
            $adminUser->roles()->attach($adminRole->id);

            $liderUser = User::create([
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'email' => env('LIDER_EMAIL', 'carlos.rodriguez@uts.edu.co'),
                'password' => Hash::make($liderPassword),
                'orcid' => '0000-0001-2345-6789',
                'cvlac' => '0000000001',
                'activo' => true,
            ]);
            $liderUser->roles()->attach($liderRole->id);

            $coordinadorUser = User::create([
                'nombre' => 'María',
                'apellido' => 'González',
                'email' => env('COORDINADOR_EMAIL', 'maria.gonzalez@uts.edu.co'),
                'password' => Hash::make($coordinadorPassword),
                'orcid' => '0000-0002-3456-7890',
                'cvlac' => '0000000002',
                'activo' => true,
            ]);
            $coordinadorUser->roles()->attach($coordinadorRole->id);

            $directorUser = User::create([
                'nombre' => 'Jorge',
                'apellido' => 'Martínez',
                'email' => env('DIRECTOR_EMAIL', 'jorge.martinez@uts.edu.co'),
                'password' => Hash::make($directorPassword),
                'orcid' => '0000-0003-4567-8901',
                'cvlac' => '0000000003',
                'activo' => true,
            ]);
            $directorUser->roles()->attach($directorRole->id);

            $institucion = Institucion::create([
                'nombre' => 'Unidades Tecnológicas de Santander',
                'sigla' => 'UTS',
                'nit' => '900123456-7',
                'tipo' => 'Universidad',
                'direccion' => 'Calle 12 # 12-45',
                'ciudad' => 'Bucaramanga',
                'pais' => 'Colombia',
                'activo' => true,
            ]);

            Vinculacion::create([
                'user_id' => $liderUser->id,
                'institucion_id' => $institucion->id,
                'cargo' => 'Docente Investigador',
                'fecha_inicio' => '2020-01-15',
                'fecha_fin' => null,
                'activo' => true,
            ]);

            Vinculacion::create([
                'user_id' => $coordinadorUser->id,
                'institucion_id' => $institucion->id,
                'cargo' => 'Docente',
                'fecha_inicio' => '2021-03-01',
                'fecha_fin' => null,
                'activo' => true,
            ]);

            Vinculacion::create([
                'user_id' => $directorUser->id,
                'institucion_id' => $institucion->id,
                'cargo' => 'Docente Investigador',
                'fecha_inicio' => '2019-08-01',
                'fecha_fin' => null,
                'activo' => true,
            ]);

            $grupo = Grupo::create([
                'codigo' => 'UTS-GI-001',
                'nombre' => 'Grupo de Investigación en Inteligencia Artificial',
                'lider_id' => $liderUser->id,
                'categoria' => 'A',
                'fecha_creacion' => '2018-05-20',
                'enlace_gruplac' => 'https://scienti.minciencias.gov.co/gruplac/jsp/visualiza/visualizagr.jsp?nro=00000000001',
                'descripcion' => 'Investigación en IA aplicada a sectores productivos',
                'activo' => true,
            ]);
            $grupo->miembros()->attach($liderUser->id, ['rol' => 'Líder', 'fecha_inicio' => '2018-05-20']);

            $linea1 = LineaInvestigacion::create([
                'grupo_id' => $grupo->id,
                'nombre' => 'Machine Learning Aplicado',
                'descripcion' => 'Desarrollo de modelos de ML para predicción y clasificación',
                'activo' => true,
            ]);

            $linea2 = LineaInvestigacion::create([
                'grupo_id' => $grupo->id,
                'nombre' => 'Procesamiento de Lenguaje Natural',
                'descripcion' => 'NLP para análisis de texto y chatbots',
                'activo' => true,
            ]);

            $planTrabajo = PlanTrabajo::create([
                'grupo_id' => $grupo->id,
                'version' => '2024-2028',
                'fecha_inicio' => '2024-01-01',
                'fecha_fin' => '2028-12-31',
                'objetivo_general' => 'Consolidar el grupo como referente en IA en la región',
                'activo' => true,
            ]);

            $semillero = Semillero::create([
                'nombre' => 'Semillero de Investigación en IA',
                'coordinador_id' => $coordinadorUser->id,
                'enfoque' => 'Formación en machine learning y deep learning',
                'fecha_creacion' => '2020-03-15',
                'categoria' => 'B',
                'descripcion' => 'Formación de estudiantes en IA aplicada',
                'activo' => true,
            ]);
            $semillero->integrantes()->attach($coordinadorUser->id, ['rol' => 'Coordinador', 'fecha_inicio' => '2020-03-15']);
            $semillero->grupos()->attach($grupo->id, ['fecha_articulacion' => '2020-03-15']);

            $proyecto = Proyecto::create([
                'titulo' => 'Sistema de Predicción de Riesgos Agrícolas usando Machine Learning',
                'objetivo' => 'Desarrollar un sistema predictivo para optimizar cultivos en Santander',
                'tipo' => 'Investigación y Desarrollo',
                'convocatoria' => 'Convocatoria 808-2023',
                'linea_investigacion_id' => $linea1->id,
                'fecha_inicio' => '2023-08-01',
                'fecha_fin' => '2024-12-31',
                'estado' => 'En Ejecución',
                'presupuesto_total' => 150000000,
                'descripcion' => 'Proyecto enfocado en el desarrollo de modelos predictivos para agricultura',
                'activo' => true,
            ]);
            $proyecto->grupos()->attach($grupo->id);
            $proyecto->semilleros()->attach($semillero->id);

            ParticipanteProyecto::create([
                'proyecto_id' => $proyecto->id,
                'user_id' => $directorUser->id,
                'rol' => 'Director',
                'horas_dedicacion' => 20,
                'fecha_inicio' => '2023-08-01',
                'activo' => true,
            ]);

            ParticipanteProyecto::create([
                'proyecto_id' => $proyecto->id,
                'user_id' => $liderUser->id,
                'rol' => 'Investigador Principal',
                'horas_dedicacion' => 15,
                'fecha_inicio' => '2023-08-01',
                'activo' => true,
            ]);

            $actividad1 = Actividad::create([
                'proyecto_id' => $proyecto->id,
                'nombre' => 'Recolección de datos agrícolas',
                'descripcion' => 'Recopilación de datos históricos de cultivos en Santander',
                'fecha_inicio' => '2023-08-15',
                'fecha_fin' => '2023-10-30',
                'estado' => 'Completada',
                'porcentaje_avance' => 100,
                'activo' => true,
            ]);

            $actividad2 = Actividad::create([
                'proyecto_id' => $proyecto->id,
                'nombre' => 'Desarrollo de modelo predictivo',
                'descripcion' => 'Implementación de algoritmos de ML para predicción',
                'fecha_inicio' => '2023-11-01',
                'fecha_fin' => '2024-03-31',
                'estado' => 'En Ejecución',
                'porcentaje_avance' => 65,
                'activo' => true,
            ]);

            $versionCatalogo = VersionCatalogo::where('version', '2024.1')->first();
            $categoriaDTI = CategoriaProducto::where('tipo_categoria', 'DTI')->first();
            $subtipoSoftware = SubtipoProducto::where('codigo', 'S1')->first();

            $productoSoftware = ProductoCtei::create([
                'titulo' => 'Plataforma de Predicción Agrícola UTS',
                'descripcion' => 'Sistema web para predicción de riesgos en cultivos usando machine learning',
                'fecha_publicacion' => '2024-03-15',
                'subtipo_id' => $subtipoSoftware->id,
                'linea_investigacion_id' => $linea1->id,
                'proyecto_id' => $proyecto->id,
                'estado' => 'Reportado',
                'categoria_principal' => 'DTI',
                'activo' => true,
            ]);
            $productoSoftware->autores()->attach($directorUser->id, ['rol_autor' => 'Principal']);
            $productoSoftware->autores()->attach($liderUser->id, ['rol_autor' => 'Co-autor']);
            $productoSoftware->grupos()->attach($grupo->id);
            $productoSoftware->semilleros()->attach($semillero->id);

            $software = SoftwareRegistrado::create([
                'producto_id' => $productoSoftware->id,
                'nombre' => 'UTS-PredicAgri',
                'version' => '1.0.0',
                'anio' => 2024,
                'tipo' => 'Plataforma Web',
                'titular' => 'Unidades Tecnológicas de Santander',
                'licencia' => 'MIT',
                'disponibilidad' => 'Acceso restringido',
                'descripcion' => 'Plataforma web para predicción agrícola',
                'innovacion_nivel' => 'Alto',
                'registro_dnda' => false,
                'activo' => true,
            ]);

            $faseAnalisis = FaseSoftware::create([
                'software_id' => $software->id,
                'nombre' => 'Análisis',
                'descripcion' => 'Análisis de requerimientos y estudio de viabilidad',
                'estado' => 'Completada',
                'fecha_inicio' => '2023-08-01',
                'fecha_fin' => '2023-09-15',
                'documentos_requeridos' => json_encode([
                    'Especificación de requerimientos',
                    'Matriz de trazabilidad',
                    'Análisis de viabilidad técnica',
                ]),
                'activo' => true,
            ]);

            DocumentoSoftware::create([
                'fase_id' => $faseAnalisis->id,
                'nombre' => 'Documento de Requerimientos',
                'tipo' => 'PDF',
                'ruta_archivo' => 'software/analisis/requerimientos.pdf',
                'descripcion' => 'Especificación detallada de requerimientos funcionales y no funcionales',
                'activo' => true,
            ]);

            $faseDiseno = FaseSoftware::create([
                'software_id' => $software->id,
                'nombre' => 'Diseño',
                'descripcion' => 'Diseño arquitectónico y de interfaz de usuario',
                'estado' => 'Completada',
                'fecha_inicio' => '2023-09-16',
                'fecha_fin' => '2023-11-30',
                'documentos_requeridos' => json_encode([
                    'Diagrama de arquitectura',
                    'Diseño de base de datos',
                    'Mockups de interfaz',
                ]),
                'activo' => true,
            ]);

            DocumentoSoftware::create([
                'fase_id' => $faseDiseno->id,
                'nombre' => 'Diagrama de Arquitectura',
                'tipo' => 'PNG',
                'ruta_archivo' => 'software/diseno/arquitectura.png',
                'descripcion' => 'Diagrama de arquitectura del sistema',
                'activo' => true,
            ]);

            $faseImplementacion = FaseSoftware::create([
                'software_id' => $software->id,
                'nombre' => 'Implementación',
                'descripcion' => 'Desarrollo del código y pruebas unitarias',
                'estado' => 'Completada',
                'fecha_inicio' => '2023-12-01',
                'fecha_fin' => '2024-02-28',
                'documentos_requeridos' => json_encode([
                    'Código fuente',
                    'Documentación técnica',
                    'Pruebas unitarias',
                ]),
                'activo' => true,
            ]);

            DocumentoSoftware::create([
                'fase_id' => $faseImplementacion->id,
                'nombre' => 'Manual Técnico',
                'tipo' => 'PDF',
                'ruta_archivo' => 'software/implementacion/manual_tecnico.pdf',
                'descripcion' => 'Documentación técnica del código fuente',
                'activo' => true,
            ]);

            $faseValidacion = FaseSoftware::create([
                'software_id' => $software->id,
                'nombre' => 'Validación',
                'descripcion' => 'Pruebas de integración, validación con usuarios y aceptación',
                'estado' => 'Completada',
                'fecha_inicio' => '2024-03-01',
                'fecha_fin' => '2024-03-15',
                'documentos_requeridos' => json_encode([
                    'Reporte de pruebas',
                    'Validación con usuarios',
                    'Certificado de aceptación',
                ]),
                'activo' => true,
            ]);

            DocumentoSoftware::create([
                'fase_id' => $faseValidacion->id,
                'nombre' => 'Reporte de Validación',
                'tipo' => 'PDF',
                'ruta_archivo' => 'software/validacion/reporte_validacion.pdf',
                'descripcion' => 'Reporte de validación con usuarios finales',
                'activo' => true,
            ]);

            DB::commit();
            $this->command->info('✅ Datos de prueba creados exitosamente');
            $this->command->info('📝 Configure las credenciales en el archivo .env');
            $this->command->info('📝 Variables requeridas: ADMIN_EMAIL, ADMIN_PASSWORD, LIDER_EMAIL, LIDER_PASSWORD, etc.');
            $this->command->info('⚠️  Las contraseñas deben ser configuradas como variables de entorno por seguridad');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error al crear datos de prueba: ' . $e->getMessage());
            throw $e;
        }
    }
}
