@extends('layouts.app')

@section('title', 'Semilleros de Investigación')

@section('header-title', 'Semilleros de Investigación')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" placeholder="Buscar semilleros..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-uts-primary focus:border-transparent bg-white">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-uts-primary focus:border-transparent bg-white">
                <option value="">Todas las categorías</option>
                <option value="A">Categoría A</option>
                <option value="B">Categoría B</option>
                <option value="C">Categoría C</option>
            </select>
        </div>
        <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Semillero
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Coordinador</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Enfoque</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Fecha Creación</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Integrantes</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Grupos</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($semilleros as $semillero)
                        <tr class="hover:bg-uts-primaryLight transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-uts-text">{{ $semillero->nombre }}</div>
                                <div class="text-xs text-uts-textLight">{{ Str::limit($semillero->descripcion, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $semillero->coordinador->nombre ?? 'N/A' }} {{ $semillero->coordinador->apellido ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ Str::limit($semillero->enfoque, 30) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-green 
                                    {{ $semillero->categoria === 'A' ? 'bg-green-100 text-green-800' : 
                                       ($semillero->categoria === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $semillero->categoria }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ $semillero->fecha_creacion }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($semillero->activo)
                                    <span class="badge-green-soft bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="badge-green-soft bg-red-100 text-red-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $semillero->integrantes->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $semillero->grupos->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Gestionar integrantes">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Articular con grupos">
                                    <i class="fas fa-link"></i>
                                </button>
                                @if($semillero->activo)
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
                    Mostrando {{ $semilleros->firstItem() }} a {{ $semilleros->lastItem() }} de {{ $semilleros->total() }} resultados
                </div>
                <div class="flex space-x-2">
                    @if($semilleros->onFirstPage())
                        <button class="badge-green-soft bg-gray-100 text-gray-400 cursor-not-allowed">
                            Anterior
                        </button>
                    @else
                        <button class="badge-green-soft hover:bg-uts-primaryLight">
                            Anterior
                        </button>
                    @endif
                    
                    @if($semilleros->hasMorePages())
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
