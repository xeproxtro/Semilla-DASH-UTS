@extends('layouts.app')

@section('title', 'Kanban - Fases de Software')

@section('header-title', 'Kanban - Fases de Software')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-uts-text">Tablero de Fases de Software</h1>
            <p class="text-sm text-uts-textLight mt-1">Gestión visual del expediente técnico de software</p>
        </div>
        <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Software
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="softwareKanban">
        <div class="kanban-column" data-phase="analisis">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                    Análisis
                </h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $softwareAnalisis->count() }}
                </span>
            </div>
            <div class="space-y-3 software-cards" data-phase="analisis">
                @foreach($softwareAnalisis as $software)
                    <div class="kanban-card" draggable="true" data-id="{{ $software->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $software->nombre }}</span>
                            <span class="text-xs text-uts-textLight">{{ $software->version }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $software->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-blue-100 text-blue-800 text-xs">
                                {{ $software->tipo }}
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $software->anio }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-phase="diseno">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                    Diseño
                </h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $softwareDiseno->count() }}
                </span>
            </div>
            <div class="space-y-3 software-cards" data-phase="diseno">
                @foreach($softwareDiseno as $software)
                    <div class="kanban-card" draggable="true" data-id="{{ $software->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $software->nombre }}</span>
                            <span class="text-xs text-uts-textLight">{{ $software->version }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $software->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-yellow-100 text-yellow-800 text-xs">
                                {{ $software->tipo }}
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $software->anio }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-phase="implementacion">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                    Implementación
                </h3>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $softwareImplementacion->count() }}
                </span>
            </div>
            <div class="space-y-3 software-cards" data-phase="implementacion">
                @foreach($softwareImplementacion as $software)
                    <div class="kanban-card" draggable="true" data-id="{{ $software->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $software->nombre }}</span>
                            <span class="text-xs text-uts-textLight">{{ $software->version }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $software->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-orange-100 text-orange-800 text-xs">
                                {{ $software->tipo }}
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $software->anio }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-phase="validacion">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-uts-success rounded-full mr-2"></span>
                    Validación
                </h3>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $softwareValidacion->count() }}
                </span>
            </div>
            <div class="space-y-3 software-cards" data-phase="validacion">
                @foreach($softwareValidacion as $software)
                    <div class="kanban-card" draggable="true" data-id="{{ $software->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $software->nombre }}</span>
                            <span class="text-xs text-uts-textLight">{{ $software->version }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $software->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-green-100 text-green-800 text-xs">
                                {{ $software->tipo }}
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $software->anio }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.software-cards');
        
        columns.forEach(column => {
            new Sortable(column, {
                group: 'software-kanban',
                animation: 150,
                ghostClass: 'kanban-card',
                dragClass: 'dragging',
                onEnd: function(evt) {
                    const card = evt.item;
                    const newPhase = evt.to.dataset.phase;
                    const softwareId = card.dataset.id;
                    
                    console.log('Software ' + softwareId + ' movido a ' + newPhase);
                    
                    // Aquí puedes agregar la llamada AJAX para actualizar el estado
                    // fetch('/api/v1/software/' + softwareId + '/fase', {
                    //     method: 'POST',
                    //     headers: {
                    //         'Content-Type': 'application/json',
                    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    //     },
                    //     body: JSON.stringify({ fase: newPhase })
                    // });
                }
            });
        });
    });
</script>
@endpush
@endsection
