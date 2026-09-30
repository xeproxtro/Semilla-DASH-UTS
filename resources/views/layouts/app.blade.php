<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SEMILLA-DASH UTS') - Gestión de Investigación</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        uts: {
                            primary: '#10b981', // Verde institucional suave
                            primaryDark: '#059669',
                            primaryLight: '#d1fae5',
                            secondary: '#3b82f6',
                            accent: '#f59e0b',
                            success: '#10b981',
                            danger: '#ef4444',
                            warning: '#f59e0b',
                            background: '#f0fdf4', // Fondo verde muy suave
                            surface: '#ffffff',
                            text: '#1f2937',
                            textLight: '#6b7280',
                            border: '#e5e7eb',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        .menu-item:hover {
            background-color: rgba(16, 185, 129, 0.1);
        }
        .menu-item.active {
            background-color: rgba(16, 185, 129, 0.15);
            border-left: 4px solid #10b981;
        }
        .metric-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .kanban-column {
            min-height: 400px;
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
        }
        .kanban-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: grab;
            transition: all 0.2s ease;
        }
        .kanban-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .kanban-card.dragging {
            opacity: 0.5;
            transform: rotate(3deg);
        }
        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-green-soft {
            background-color: #ecfdf5;
            color: #047857;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-uts-background font-sans">
    <div class="flex h-screen overflow-hidden">
        @auth
            @if(auth()->user()->roles->isNotEmpty())
                @php
                    $userRole = auth()->user()->roles->first()->slug;
                @endphp
                
                @if($userRole === 'administrador' || $userRole === 'lider_grupo' || $userRole === 'gestor_investigacion' || $userRole === 'aval_institucional')
                    @include('layouts.sidebar')
                @endif
            @endif
        @endauth

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('layouts.header')
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-uts-background p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span class="block sm:inline">{{ session('warning') }}</span>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="font-medium">Por favor corrija los siguientes errores:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
