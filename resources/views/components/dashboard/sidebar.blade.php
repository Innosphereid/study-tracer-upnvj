@props(['activeMenu' => null])

<div x-data="{ 
        isOpen: true,
        activeMenu: '{{ $activeMenu }}',
        isMobile: window.innerWidth < 768
    }" @toggle-sidebar.window="isOpen = !isOpen"
    @resize.window="isMobile = window.innerWidth < 768; if (!isMobile) isOpen = true"
    :class="{'md:w-64': isOpen, 'md:w-20': !isOpen, 'w-0': isMobile && !isOpen, 'w-64': isMobile && isOpen}"
    class="bg-indigo-800 flex flex-col transition-all duration-300 ease-in-out z-20 fixed md:relative h-screen md:h-auto">

    <!-- Sidebar header -->
    <div class="flex items-center justify-between px-4 py-5">
        <div class="flex items-center">
            <img src="{{ asset('logo-upnvj.png') }}" alt="UPNVJ Logo" class="h-8">
            <span class="text-white ml-3 font-semibold text-lg" x-show="isOpen" x-transition>Tracer Study</span>
        </div>
        <button @click="isOpen = !isOpen" class="text-indigo-200 hover:text-white focus:outline-none hidden md:block">
            <svg x-show="isOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg x-show="!isOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>
        <!-- Mobile Close button -->
        <button @click="isOpen = false" class="text-indigo-200 hover:text-white focus:outline-none md:hidden"
            x-show="isMobile && isOpen">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Sidebar content -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="mt-2">
            <!-- Dashboard -->
            <x-dashboard.sidebar-item icon="home" label="Dashboard"
                link="{{ auth()->user()->role === 'superadmin' ? route('superadmin.dashboard') : route('admin.dashboard') }}"
                :active="$activeMenu === 'dashboard'" />

            <!-- Kelola Kuesioner -->
            <x-dashboard.sidebar-dropdown icon="clipboard-list" label="Kelola Kuesioner"
                :active="str_starts_with($activeMenu ?? '', 'kuesioner')">
                <x-dashboard.sidebar-item label="Buat Kuesioner" link="/kuesioner/create" :is-dropdown-item="true"
                    :active="$activeMenu === 'kuesioner.create'" />
                <x-dashboard.sidebar-item label="Daftar Kuesioner" link="/kuesioner" :is-dropdown-item="true"
                    :active="$activeMenu === 'kuesioner.list'" />
                <x-dashboard.sidebar-item label="Template Kuesioner" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'kuesioner.template'" />
            </x-dashboard.sidebar-dropdown>

            <!-- Kelola Alumni -->
            <x-dashboard.sidebar-dropdown icon="users" label="Kelola Alumni"
                :active="str_starts_with($activeMenu ?? '', 'alumni')">
                <x-dashboard.sidebar-item label="Data Alumni" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'alumni.data'" />
                <x-dashboard.sidebar-item label="Import Data" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'alumni.import'" />
                <x-dashboard.sidebar-item label="Statistik Alumni" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'alumni.statistics'" />
            </x-dashboard.sidebar-dropdown>

            <!-- Kelola Admin (Hanya untuk Superadmin) -->
            @if(auth()->user()->role === 'superadmin')
            <x-dashboard.sidebar-dropdown icon="shield-check" label="Kelola Admin"
                :active="str_starts_with($activeMenu ?? '', 'admin')">
                <x-dashboard.sidebar-item label="Daftar Admin" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'admin.list'" />
                <x-dashboard.sidebar-item label="Tambah Admin" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'admin.create'" />
                <x-dashboard.sidebar-item label="Log Aktivitas Admin" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'admin.logs'" />
            </x-dashboard.sidebar-dropdown>
            @endif

            <!-- Laporan -->
            <x-dashboard.sidebar-dropdown icon="chart-bar" label="Laporan"
                :active="str_starts_with($activeMenu ?? '', 'laporan')">
                <x-dashboard.sidebar-item label="Laporan Tracer Study" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'laporan.tracer'" />
                <x-dashboard.sidebar-item label="Export Data" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'laporan.export'" />
                <x-dashboard.sidebar-item label="Visualisasi Data" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'laporan.visualization'" />
            </x-dashboard.sidebar-dropdown>

            <!-- Pengaturan (Berbeda untuk Admin dan Superadmin) -->
            <x-dashboard.sidebar-dropdown icon="cog" label="Pengaturan"
                :active="str_starts_with($activeMenu ?? '', 'pengaturan')">
                <x-dashboard.sidebar-item label="Profil Pengguna" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'pengaturan.profile'" />

                @if(auth()->user()->role === 'superadmin')
                <x-dashboard.sidebar-item label="Profil Institusi" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'pengaturan.institution'" />
                <x-dashboard.sidebar-item label="Email Template" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'pengaturan.email'" />
                <x-dashboard.sidebar-item label="Pengaturan Sistem" link="#" :is-dropdown-item="true"
                    :active="$activeMenu === 'pengaturan.system'" />
                @endif
            </x-dashboard.sidebar-dropdown>
        </nav>
    </div>
</div>

<!-- Overlay to close sidebar on mobile when sidebar is open -->
<div x-data="{}" @toggle-sidebar.window="$el.classList.toggle('block'); $el.classList.toggle('hidden');"
    @resize.window="window.innerWidth >= 768 ? $el.classList.add('hidden') : null"
    class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden md:hidden" @click="$dispatch('toggle-sidebar')">
</div>