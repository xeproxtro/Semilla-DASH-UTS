<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center space-x-4">
            <button id="sidebarToggle" class="lg:hidden text-uts-text hover:text-uts-primary">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="text-xl font-semibold text-uts-text">@yield('header-title', 'Dashboard')</h2>
        </div>

        <div class="flex items-center space-x-4">
            <div class="relative">
                <button class="text-uts-textLight hover:text-uts-primary focus:outline-none">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-uts-danger text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                </button>
            </div>

            <div class="relative">
                <button class="flex items-center space-x-3 bg-uts-primaryLight rounded-full px-4 py-2 hover:bg-uts-primaryLight transition-colors focus:outline-none">
                    <div class="w-8 h-8 bg-uts-primary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
                        {{ substr(auth()->user()->nombre, 0, 1) }}{{ substr(auth()->user()->apellido, 0, 1) }}
                    </div>
                    <div class="hidden md:block">
                        <p class="text-sm font-semibold text-uts-text">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
                        <p class="text-xs text-uts-textLight">{{ auth()->user()->roles->first()->nombre ?? 'Sin rol' }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-uts-textLight"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        const sidebar = document.querySelector('aside');
        sidebar.classList.toggle('-translate-x-full');
    });
</script>
