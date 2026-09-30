<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Integrante de Semillero',
                'slug' => 'integrante_semillero',
                'descripcion' => 'Registra participación y evidencias propias',
                'permisos' => [
                    'evidencias.create',
                    'evidencias.view.own',
                    'revisiones.view.own',
                    'revisiones.update.own',
                ],
            ],
            [
                'nombre' => 'Coordinador de Semillero',
                'slug' => 'coordinador_semillero',
                'descripcion' => 'Gestiona el plan formativo y a los integrantes',
                'permisos' => [
                    'semilleros.manage.own',
                    'semilleros.members.manage',
                    'evidencias.manage.own',
                    'revisiones.manage.own',
                    'proyectos.view.own',
                ],
            ],
            [
                'nombre' => 'Investigador',
                'slug' => 'investigador',
                'descripcion' => 'Mantiene referencias de sus productos',
                'permisos' => [
                    'productos.create',
                    'productos.update.own',
                    'productos.view.own',
                    'evidencias.manage.own',
                    'revisiones.view.own',
                ],
            ],
            [
                'nombre' => 'Líder de Grupo',
                'slug' => 'lider_grupo',
                'descripcion' => 'Administra el plan estratégico del grupo',
                'permisos' => [
                    'grupos.manage.own',
                    'grupos.members.manage',
                    'lineas.manage.own',
                    'planes.manage.own',
                    'semilleros.view.own',
                    'proyectos.manage.own',
                    'productos.view.own',
                ],
            ],
            [
                'nombre' => 'Director de Proyecto',
                'slug' => 'director_proyecto',
                'descripcion' => 'Gestiona cronograma y presupuesto',
                'permisos' => [
                    'proyectos.manage.own',
                    'actividades.manage.own',
                    'participantes.manage.own',
                    'financiacion.manage.own',
                    'avances.manage.own',
                ],
            ],
            [
                'nombre' => 'Gestor de Investigación',
                'slug' => 'gestor_investigacion',
                'descripcion' => 'Revisa evidencias y normaliza metadatos',
                'permisos' => [
                    'revisiones.view.all',
                    'revisiones.update.all',
                    'revisiones.approve',
                    'revisiones.reject',
                    'evidencias.view.all',
                    'evidencias.validate',
                    'productos.view.all',
                    'productos.normalize',
                ],
            ],
            [
                'nombre' => 'Aval Institucional',
                'slug' => 'aval_institucional',
                'descripcion' => 'Emite decisiones formales sobre expedientes',
                'permisos' => [
                    'revisiones.aval',
                    'revisiones.view.all',
                    'cortes.manage',
                    'cortes.close',
                    'cortes.reopen',
                    'catalogos.view',
                ],
            ],
            [
                'nombre' => 'Administrador',
                'slug' => 'administrador',
                'descripcion' => 'Gestiona catálogos, ventanas, auditoría y seguridad',
                'permisos' => [
                    'users.manage',
                    'roles.manage',
                    'grupos.manage.all',
                    'semilleros.manage.all',
                    'proyectos.manage.all',
                    'productos.manage.all',
                    'catalogos.manage',
                    'ventanas.manage',
                    'cortes.manage.all',
                    'auditoria.view',
                    'settings.manage',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'nombre' => $role['nombre'],
                    'descripcion' => $role['descripcion'],
                    'activo' => true,
                ]
            );
        }

        $this->command->info('✅ Roles RBAC creados exitosamente');
    }
}
