@extends('layouts.app')

@section('title', 'Kanban - Flujo de Avales')

@section('header-title', 'Kanban - Flujo de Avales')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-uts-text">Tablero de Flujo de Avales</h1>
            <p class="text-sm text-uts-textLight mt-1">Gestión visual del proceso de revisión y aprobación de expedientes</p>
        </div>
        <div class="flex space-x-3">
            <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
                <i class="fas fa-filter mr-2"></i>
                Filtrar
            </button>
            <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4" id="revisionesKanban">
        <div class="kanban-column" data-state="borrador">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
                    Borrador
                </h3>
                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $revisionesBorrador->count() }}
                </span>
            </div>
            <div class="space-y-3 revision-cards" data-state="borrador">
                @foreach($revisionesBorrador as $revision)
                    <div class="kanban-card" draggable="true" data-id="{{ $revision->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $revision->producto->subtipo->nombre ?? 'N/A' }}</span>
                            <span class="text-xs text-uts-textLight">{{ $revision->created_at }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $revision->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-gray-100 text-gray-800 text-xs">
                                {{ $revision->porcentaje_completitud }}%
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $revision->producto->autores->first()->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-state="enviado">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                    Enviado
                </h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $revisionesEnviado->count() }}
                </span>
            </div>
            <div class="space-y-3 revision-cards" data-state="enviado">
                @foreach($revisionesEnviado as $revision)
                    <div class="kanban-card" draggable="true" data-id="{{ $revision->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $revision->producto->subtipo->nombre ?? 'N/A' }}</span>
                            <span class="text-xs text-uts-textLight">{{ $revision->fecha_estado }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $revision->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-blue-100 text-blue-800 text-xs">
                                {{ $revision->porcentaje_completitud }}%
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $revision->producto->autores->first()->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-state="revision">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                    En Revisión
                </h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $revisionesRevision->count() }}
                </span>
            </div>
            <div class="space-y-3 revision-cards" data-state="revision">
                @foreach($revisionesRevision as $revision)
                    <div class="kanban-card" draggable="true" data-id="{{ $revision->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $revision->producto->subtipo->nombre ?? 'N/A' }}</span>
                            <span class="text-xs text-uts-textLight">{{ $revision->fecha_estado }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $revision->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-yellow-100 text-yellow-800 text-xs">
                                {{ $revision->porcentaje_completitud }}%
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $revision->producto->autores->first()->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-state="devuelto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                    Devuelto
                </h3>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $revisionesDevuelto->count() }}
                </span>
            </div>
            <div class="space-y-3 revision-cards" data-state="devuelto">
                @foreach($revisionesDevuelto as $revision)
                    <div class="kanban-card" draggable="true" data-id="{{ $revision->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $revision->producto->subtipo->nombre ?? 'N/A' }}</span>
                            <span class="text-xs text-uts-textLight">{{ $revision->fecha_estado }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $revision->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-orange-100 text-orange-800 text-xs">
                                {{ $revision->porcentaje_completitud }}%
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $revision->producto->autores->first()->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="kanban-column" data-state="avalado">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-uts-text flex items-center">
                    <span class="w-3 h-3 bg-uts-success rounded-full mr-2"></span>
                    Avalado
                </h3>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $revisionesAvalado->count() }}
                </span>
            </div>
            <div class="space-y-3 revision-cards" data-state="avalado">
                @foreach($revisionesAvalado as $revision)
                    <div class="kanban-card" draggable="true" data-id="{{ $revision->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-uts-textLight">{{ $revision->producto->subtipo->nombre ?? 'N/A' }}</span>
                            <span class="text-xs text-uts-textLight">{{ $revision->fecha_estado }}</span>
                        </div>
                        <p class="text-sm font-medium text-uts-text mb-2">{{ $revision->producto->titulo }}</p>
                        <div class="flex items-center justify-between">
                            <span class="badge-green bg-green-100 text-green-800 text-xs">
                                {{ $revision->porcentaje_completitud }}%
                            </span>
                            <span class="text-xs text-uts-textLight">{{ $revision->producto->autores->first()->nombre ?? 'N/A' }}</span>
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
        const columns = document.querySelectorAll('.revision-cards');
        
        columns.forEach(column => {
            new Sortable(column, {
                group: 'revisiones-kanban',
                animation: 150,
                ghostClass: 'kanban-card',
                dragClass: 'dragging',
                onEnd: function(evt) {
                    const card = evt.item;
                    const newState = evt.to.dataset.state;
                    const revisionId = card.dataset.id;
                    
                    console.log('Revisión ' + revisionId + ' movida a ' + newState);
                    
                    // Aquí puedes agregar la llamada AJAX para actualizar el estado
                    // fetch('/api/v1/revisiones/' + revisionId + '/estado', {
                    //     method: 'POST',
                    //     headers: {
                    //         'Content-Type': 'application/json',
                    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    //     },
                    //     body: JSON.stringify({ estado: newState })
                    // });
                }
            });
        });
    });
</script>
@endpush
@endsection
