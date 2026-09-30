@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('header-title', 'Detalle del Producto CTeI')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $producto->titulo }}</h1>
                <p class="text-sm text-gray-500">{{ $producto->subtipo->nombre ?? 'Sin subtipo' }}</p>
            </div>
            <div class="flex space-x-2">
                <span class="px-3 py-1 text-sm font-medium rounded-full 
                    {{ $producto->estado === 'Reportado' ? 'bg-green-100 text-green-800' : 
                       ($producto->estado === 'Avalado' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ $producto->estado }}
                </span>
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ $producto->categoria_principal }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Información General</h3>
                <dl class="space-y-2">
                    <div class="flex">
                        <dt class="w-1/3 text-sm text-gray-500">Categoría:</dt>
                        <dd class="w-2/3 text-sm text-gray-900">{{ $producto->categoria_principal }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-1/3 text-sm text-gray-500">Subtipo:</dt>
                        <dd class="w-2/3 text-sm text-gray-900">{{ $producto->subtipo->nombre ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-1/3 text-sm text-gray-500">Fecha Publicación:</dt>
                        <dd class="w-2/3 text-sm text-gray-900">{{ $producto->fecha_publicacion }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-1/3 text-sm text-gray-500">Estado Ventana:</dt>
                        <dd class="w-2/3 text-sm">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $estadoVentana === 'dentro_ventana' ? 'bg-green-100 text-green-800' : 
                                   ($estadoVentana === 'proximo_vencer' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $estadoVentana ?? 'No determinable' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Autores</h3>
                <div class="space-y-2">
                    @foreach($producto->autores as $autor)
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                            <div class="w-8 h-8 bg-uts-secondary rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $autor->nombre }} {{ $autor->apellido }}</p>
                                <p class="text-xs text-gray-500">{{ $autor->pivot->rol_autor ?? 'Autor' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Descripción</h3>
            <p class="text-sm text-gray-700">{{ $producto->descripcion }}</p>
        </div>
    </div>

    @if($producto->subtipo && $producto->subtipo->codigo === 'S1')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">
                <i class="fas fa-laptop-code mr-2"></i>
                Expediente de Software
            </h2>

            @if($software)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Información del Software</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Nombre</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->nombre }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Versión</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->version }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Año</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->anio }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Tipo</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->tipo }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Licencia</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->licencia }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-500">Disponibilidad</p>
                            <p class="text-sm font-medium text-gray-900">{{ $software->disponibilidad }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Fases Obligatorias del Expediente</h3>
                    
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        @foreach($fases as $index => $fase)
                            <div class="relative pl-12 pb-8">
                                <div class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $fase->estado === 'Completada' ? 'bg-green-500' : 
                                       ($fase->estado === 'En Ejecución' ? 'bg-blue-500' : 'bg-gray-300') }}">
                                    <i class="fas {{ $fase->estado === 'Completada' ? 'fa-check' : 
                                                    ($fase->estado === 'En Ejecución' ? 'fa-spinner fa-spin' : 'fa-clock') }} text-white text-sm"></i>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">{{ $fase->nombre }}</h4>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $fase->estado === 'Completada' ? 'bg-green-100 text-green-800' : 
                                               ($fase->estado === 'En Ejecución' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $fase->estado }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">{{ $fase->descripcion }}</p>
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <span><i class="fas fa-calendar-alt mr-1"></i>{{ $fase->fecha_inicio }}</span>
                                        <span><i class="fas fa-calendar-check mr-1"></i>{{ $fase->fecha_fin ?? 'En curso' }}</span>
                                    </div>
                                    
                                    @if($fase->documentos->isNotEmpty())
                                        <div class="mt-3">
                                            <p class="text-xs font-medium text-gray-700 mb-2">Documentos:</p>
                                            <div class="space-y-1">
                                                @foreach($fase->documentos as $documento)
                                                    <div class="flex items-center space-x-2 text-sm">
                                                        <i class="fas fa-file-pdf text-red-500"></i>
                                                        <span>{{ $documento->nombre }}</span>
                                                        <button class="text-uts-secondary hover:text-uts-primary">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Completitud del Expediente</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-700">Fases Completadas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $completitudFases }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-uts-success h-2.5 rounded-full" style="width: {{ $completitudFases }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-laptop-code text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No hay información de software registrada</p>
                    <button class="mt-4 px-4 py-2 bg-uts-primary text-white rounded-lg hover:bg-blue-700">
                        Registrar Software
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Evidencias</h3>
        
        @if($evidencias->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($evidencias as $evidencia)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $evidencia->tipo === 'Archivo' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $evidencia->tipo }}
                            </span>
                            @if($evidencia->validado)
                                <span class="text-green-500"><i class="fas fa-check-circle"></i></span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-900 mb-1">{{ $evidencia->nombre }}</p>
                        <p class="text-xs text-gray-500 mb-2">{{ $evidencia->tamano_formateado }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ $evidencia->created_at }}</span>
                            <button class="text-uts-secondary hover:text-uts-primary">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay evidencias registradas</p>
                <button class="mt-4 px-4 py-2 bg-uts-primary text-white rounded-lg hover:bg-blue-700">
                    Subir Evidencia
                </button>
            </div>
        @endif
    </div>
</div>
@endsection
