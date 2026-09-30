@extends('layouts.app')

@section('title', 'Proyectos de Investigación')

@section('header-title', 'Proyectos de Investigación')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" placeholder="Buscar proyectos..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-uts-primary focus:border-transparent bg-white">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-uts-primary focus:border-transparent bg-white">
                <option value="">Todos los estados</option>
                <option value="En Ejecución">En Ejecución</option>
                <option value="Finalizado">Finalizado</option>
                <option value="Suspendido">Suspendido</option>
                <option value="Cancelado">Cancelado</option>
            </select>
            <select class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-uts-primary focus:border-transparent bg-white">
                <option value="">Todos los tipos</option>
                <option value="Investigación y Desarrollo">Investigación y Desarrollo</option>
                <option value="Investigación-Creación">Investigación-Creación</option>
                <option value="ID+I">ID+I</option>
                <option value="Extensión">Extensión</option>
                <option value="Proyectos Formativos">Proyectos Formativos</option>
            </select>
        </div>
        <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Proyecto
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Director</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Fecha Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Presupuesto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Grupos</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($proyectos as $proyecto)
                        <tr class="hover:bg-uts-primaryLight transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-uts-text">{{ $proyecto->titulo }}</div>
                                <div class="text-xs text-uts-textLight">{{ $proyecto->convocatoria ?? 'Sin convocatoria' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $proyecto->tipo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $proyecto->director->nombre ?? 'N/A' }} {{ $proyecto->director->apellido ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ $proyecto->fecha_inicio }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ $proyecto->fecha_fin ?? 'En curso' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-green 
                                    {{ $proyecto->estado === 'En Ejecución' ? 'bg-blue-100 text-blue-800' : 
                                       ($proyecto->estado === 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                       ($proyecto->estado === 'Suspendido' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ $proyecto->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                ${{ number_format($proyecto->presupuesto_total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $proyecto->grupos->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Gestionar participantes">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Actividades">
                                    <i class="fas fa-tasks"></i>
                                </button>
                                @if($proyecto->activo)
                                    <button class="text-uts-warning hover:text-yellow-700" title="Desactivar">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                @else
                                    <button class="text-gray-400 hover:text-gray-600" title="Activar">
                                        <i class="fas fa-toggle-off"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-uts-textLight">
                    Mostrando {{ $proyectos->firstItem() }} a {{ $proyectos->lastItem() }} de {{ $proyectos->total() }} resultados
                </div>
                <div class="flex space-x-2">
                    @if($proyectos->onFirstPage())
                        <button class="badge-green-soft bg-gray-100 text-gray-400 cursor-not-allowed">
                            Anterior
                        </button>
                    @else
                        <button class="badge-green-soft hover:bg-uts-primaryLight">
                            Anterior
                        </button>
                    @endif
                    
                    @if($proyectos->hasMorePages())
                        <button class="badge-green-soft hover:bg-uts-primaryLight">
                            Siguiente
                        </button>
                    @else
                        <button class="badge-green-soft bg-gray-100 text-gray-400 cursor-not-allowed">
                            Siguiente
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
