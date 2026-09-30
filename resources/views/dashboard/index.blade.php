@extends('layouts.app')

@section('title', 'Dashboard')

@section('header-title', 'Dashboard Principal')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="metric-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-uts-primaryLight rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-uts-primary text-xl"></i>
                </div>
                <span class="text-xs text-uts-textLight">+2 este mes</span>
            </div>
            <p class="text-sm text-uts-textLight font-medium">Grupos Activos</p>
            <p class="text-3xl font-bold text-uts-text mt-1">{{ $resumen['grupos']['total'] ?? 0 }}</p>
        </div>

        <div class="metric-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-uts-primaryLight rounded-xl flex items-center justify-center">
                    <i class="fas fa-seedling text-uts-primary text-xl"></i>
                </div>
                <span class="text-xs text-uts-textLight">+1 este mes</span>
            </div>
            <p class="text-sm text-uts-textLight font-medium">Semilleros Activos</p>
            <p class="text-3xl font-bold text-uts-text mt-1">{{ $resumen['semilleros']['total'] ?? 0 }}</p>
        </div>

        <div class="metric-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-uts-primaryLight rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-uts-primary text-xl"></i>
                </div>
                <span class="text-xs text-uts-textLight">+5 este mes</span>
            </div>
            <p class="text-sm text-uts-textLight font-medium">Productos Avalados</p>
            <p class="text-3xl font-bold text-uts-text mt-1">{{ $portafolio['total'] ?? 0 }}</p>
        </div>

        <div class="metric-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-uts-danger text-xl"></i>
                </div>
                <span class="text-xs text-uts-textLight">Requieren atención</span>
            </div>
            <p class="text-sm text-uts-textLight font-medium">Por Vencer</p>
            <p class="text-3xl font-bold text-uts-text mt-1">{{ $alertas['productos_ventana']['proximos_vencer'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-uts-text mb-4">Portafolio CTeI por Categoría</h3>
            <div class="h-64">
                <canvas id="cteiChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-uts-text mb-4">Alertas de Ventana de Observación</h3>
            <div class="h-64">
                <canvas id="ventanaChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-uts-text">Alertas Críticas Pendientes</h3>
            <button class="badge-green-soft hover:bg-uts-primaryLight transition-colors">
                Ver todas
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Prioridad</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-uts-textLight uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($alertas['alertas']['ultimas'] ?? [] as $alerta)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-green 
                                    {{ $alerta['tipo_alerta'] === 'Producto_Vencer' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $alerta['tipo_alerta'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-text">
                                {{ $alerta['producto']['titulo'] ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge-green 
                                    {{ $alerta['prioridad'] === 'Critica' ? 'bg-red-100 text-red-800' : 
                                       ($alerta['prioridad'] === 'Alta' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $alerta['prioridad'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-uts-textLight">
                                {{ $alerta['fecha_alerta'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($alerta['resuelta'])
                                    <span class="badge-green-soft bg-green-100 text-green-800">
                                        Resuelta
                                    </span>
                                @else
                                    <span class="badge-green-soft bg-red-100 text-red-800">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="text-uts-primary hover:text-uts-primaryDark mr-2">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-uts-success hover:text-green-700">
                                    <i class="fas fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-uts-text mb-4">Productos Próximos a Vencer</h3>
            <div class="space-y-3">
                @foreach($alertas['productos_ventana']['ultimos_proximos'] ?? [] as $producto)
                    <div class="flex items-center justify-between p-3 bg-uts-primaryLight rounded-xl">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-uts-text">{{ $producto['titulo'] }}</p>
                            <p class="text-xs text-uts-textLight">{{ $producto['subtipo']['nombre'] ?? 'N/A' }}</p>
                        </div>
                        <span class="badge-green bg-yellow-100 text-yellow-800">
                            Próximo
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-uts-text mb-4">Últimos Grupos</h3>
            <div class="space-y-3">
                @foreach($resumen['grupos']['recientes'] ?? [] as $grupo)
                    <div class="flex items-center justify-between p-3 bg-uts-primaryLight rounded-xl">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-uts-text">{{ $grupo['nombre'] }}</p>
                            <p class="text-xs text-uts-textLight">{{ $grupo['codigo'] }}</p>
                        </div>
                        <span class="badge-green">
                            {{ $grupo['categoria'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-uts-text mb-4">Últimos Semilleros</h3>
            <div class="space-y-3">
                @foreach($resumen['semilleros']['recientes'] ?? [] as $semillero)
                    <div class="flex items-center justify-between p-3 bg-uts-primaryLight rounded-xl">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-uts-text">{{ $semillero['nombre'] }}</p>
                            <p class="text-xs text-uts-textLight">{{ $semillero['enfoque'] }}</p>
                        </div>
                        <span class="badge-green">
                            {{ $semillero['categoria'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const cteiData = @json($portafolio['por_categoria'] ?? []);
    const ventanaData = @json($alertas['productos_ventana'] ?? []);

    const cteiCtx = document.getElementById('cteiChart').getContext('2d');
    new Chart(cteiCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(cteiData),
            datasets: [{
                data: Object.values(cteiData),
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    const ventanaCtx = document.getElementById('ventanaChart').getContext('2d');
    new Chart(ventanaCtx, {
        type: 'bar',
        data: {
            labels: ['Dentro Ventana', 'Próximo Vencer', 'Fuera Ventana'],
            datasets: [{
                label: 'Productos',
                data: [
                    ventanaData['proximos_vencer'] ?? 0,
                    ventanaData['proximos_vencer'] ?? 0,
                    ventanaData['fuera_ventana'] ?? 0
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
