@extends('layouts.app')

@section('title', 'Grupos de Investigación')

@section('header-title', 'Grupos de Investigación')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" placeholder="Buscar grupos..." 
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
            Nuevo Grupo
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Líder</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Fecha Creación</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Miembros</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($grupos as $grupo)
                        <tr class="hover:bg-uts-primaryLight transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-uts-text">
                                {{ $grupo->codigo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-uts-text">{{ $grupo->nombre }}</div>
                                <div class="text-xs text-uts-textLight">{{ Str::limit($grupo->descripcion, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $grupo->lider->nombre ?? 'N/A' }} {{ $grupo->lider->apellido ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-green 
                                    {{ $grupo->categoria === 'A' ? 'bg-green-100 text-green-800' : 
                                       ($grupo->categoria === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $grupo->categoria }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ $grupo->fecha_creacion }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($grupo->activo)
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
                                {{ $grupo->miembros->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-uts-primary hover:text-uts-primaryDark" title="Gestionar miembros">
                                    <i class="fas fa-users"></i>
                                </button>
                                @if($grupo->activo)
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
                    Mostrando {{ $grupos->firstItem() }} a {{ $grupos->lastItem() }} de {{ $grupos->total() }} resultados
                </div>
                <div class="flex space-x-2">
                    @if($grupos->onFirstPage())
                        <button class="badge-green-soft bg-gray-100 text-gray-400 cursor-not-allowed">
                            Anterior
                        </button>
                    @else
                        <button class="badge-green-soft hover:bg-uts-primaryLight">
                            Anterior
                        </button>
                    @endif
                    
                    @if($grupos->hasMorePages())
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
