<aside class="w-72 bg-white flex flex-col sidebar-transition shadow-lg border-r border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-uts-primary rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-flask text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-uts-text">SEMILLA-DASH</h1>
                <p class="text-xs text-uts-textLight">UTS</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-1 px-4">
            <li class="mb-6">
                <p class="text-xs font-semibold text-uts-textLight uppercase tracking-wider mb-3 px-4">
                    NAVEGACIÓN
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home w-5 text-uts-primary"></i>
                            <span class="font-medium">Inicio</span>
                        </a>
                    </li>
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('lider_grupo'))
                        <li>
                            <a href="{{ route('grupos.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                                <i class="fas fa-users w-5 text-uts-primary"></i>
                                <span class="font-medium">Grupos</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('coordinador_semillero') || auth()->user()->hasRole('lider_grupo'))
                        <li>
                            <a href="{{ route('semilleros.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('semilleros.*') ? 'active' : '' }}">
                                <i class="fas fa-seedling w-5 text-uts-primary"></i>
                                <span class="font-medium">Semilleros</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador'))
                        <li>
                            <a href="{{ route('users.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fas fa-user-friends w-5 text-uts-primary"></i>
                                <span class="font-medium">Integrantes</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            <li class="mb-6">
                <p class="text-xs font-semibold text-uts-textLight uppercase tracking-wider mb-3 px-4">
                    PRODUCCIÓN CTeI
                </p>
                <ul class="space-y-1">
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('investigador') || auth()->user()->hasRole('gestor_investigacion'))
                        <li>
                            <a href="{{ route('productos.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                                <i class="fas fa-folder-open w-5 text-uts-primary"></i>
                                <span class="font-medium">Inventario General</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('investigador'))
                        <li>
                            <a href="{{ route('productos.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors">
                                <i class="fas fa-lightbulb w-5 text-uts-primary"></i>
                                <span class="font-medium">Experiencia</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('investigador') || auth()->user()->hasRole('gestor_investigacion'))
                        <li>
                            <a href="{{ route('software.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('software.*') ? 'active' : '' }}">
                                <i class="fas fa-laptop-code w-5 text-uts-primary"></i>
                                <span class="font-medium">Software</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('gestor_investigacion') || auth()->user()->hasRole('aval_institucional'))
                        <li>
                            <a href="{{ route('cortes.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('cortes.*') ? 'active' : '' }}">
                                <i class="fas fa-clock w-5 text-uts-primary"></i>
                                <span class="font-medium">Ventanas</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('gestor_investigacion') || auth()->user()->hasRole('aval_institucional'))
                        <li>
                            <a href="{{ route('revisiones.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('revisiones.*') ? 'active' : '' }}">
                                <i class="fas fa-eye w-5 text-uts-primary"></i>
                                <span class="font-medium">Observación</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            @if(auth()->user()->hasRole('administrador'))
                <li class="mb-6">
                    <p class="text-xs font-semibold text-uts-textLight uppercase tracking-wider mb-3 px-4">
                        ADMINISTRACIÓN
                    </p>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('catalogos.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('catalogos.*') ? 'active' : '' }}">
                                <i class="fas fa-tags w-5 text-uts-primary"></i>
                                <span class="font-medium">Catálogos</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('auditoria.index') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-xl text-uts-text hover:bg-uts-primaryLight transition-colors {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                                <i class="fas fa-history w-5 text-uts-primary"></i>
                                <span class="font-medium">Auditoría</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </nav>

    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-uts-primary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
                {{ substr(auth()->user()->nombre, 0, 1) }}{{ substr(auth()->user()->apellido, 0, 1) }}
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-uts-text">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
                <p class="text-xs text-uts-textLight">{{ auth()->user()->roles->first()->nombre ?? 'Sin rol' }}</p>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-uts-primary hover:bg-uts-primaryDark rounded-xl text-white transition-colors shadow-md">
            <i class="fas fa-sign-out-alt"></i>
            <span class="font-medium">Cerrar Sesión</span>
        </a>
    </div>
</aside>
