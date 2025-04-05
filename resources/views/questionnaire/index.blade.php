@extends('layouts.dashboard')

@section('title', 'Daftar Kuesioner - TraceStudy UPNVJ')

@section('styles')
<style>
.card-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

@media (min-width: 640px) {
    .card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .card-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.table-responsive {
    overflow-x: auto;
}
</style>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Daftar Kuesioner
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Mengelola dan melacak semua kuesioner dalam sistem TraceStudy UPNVJ.
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('questionnaires.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Kuesioner Baru
                </a>
            </div>
        </div>

        <!-- Filter and Search Container -->
        <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <form id="filter-form" method="GET" action="{{ route('questionnaires.index') }}" class="space-y-6">
                <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-12">
                    <!-- Search Input -->
                    <div class="sm:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Cari Kuesioner</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                placeholder="Cari berdasarkan judul atau deskripsi">
                            @if(!empty($filters['search']))
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" onclick="clearSearch()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Filter heading -->
                    <div class="sm:col-span-12 mt-4">
                        <h3 class="text-md font-medium text-gray-900 border-b pb-2">Filter dan Pengurutan</h3>
                    </div>

                    <!-- Status Filter -->
                    <div class="sm:col-span-2">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="relative">
                            <select id="status-filter" name="status" onchange="submitForm()"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md appearance-none bg-white">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                </option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                    Publikasi</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup
                                </option>
                                <option value="template" {{ request('status') == 'template' ? 'selected' : '' }}>
                                    Template</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Period Filter -->
                    <div class="sm:col-span-2">
                        <label for="period" class="block text-sm font-medium text-gray-700">Periode</label>
                        <div class="relative">
                            <select id="period" name="period" onchange="submitForm()"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none">
                                <option value="" {{ empty($filters['period'] ?? '') ? 'selected' : '' }}>Semua</option>
                                <option value="active" {{ ($filters['period'] ?? '') == 'active' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="upcoming"
                                    {{ ($filters['period'] ?? '') == 'upcoming' ? 'selected' : '' }}>
                                    Mendatang</option>
                                <option value="expired" {{ ($filters['period'] ?? '') == 'expired' ? 'selected' : '' }}>
                                    Kedaluwarsa</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Template Filter -->
                    <div class="sm:col-span-2">
                        <label for="is_template" class="block text-sm font-medium text-gray-700">Tipe</label>
                        <div class="relative">
                            <select id="is_template" name="is_template" onchange="submitForm()"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none">
                                <option value="" {{ !isset($filters['is_template']) ? 'selected' : '' }}>Semua</option>
                                <option value="0"
                                    {{ isset($filters['is_template']) && $filters['is_template'] === '0' ? 'selected' : '' }}>
                                    Reguler</option>
                                <option value="1"
                                    {{ isset($filters['is_template']) && $filters['is_template'] === '1' ? 'selected' : '' }}>
                                    Template</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="sm:col-span-2">
                        <label for="sort" class="block text-sm font-medium text-gray-700">Urutkan</label>
                        <div class="relative">
                            <select id="sort" name="sort" onchange="submitForm()"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none">
                                <option value="newest"
                                    {{ ($filters['sort'] ?? 'newest') == 'newest' ? 'selected' : '' }}>
                                    Terbaru</option>
                                <option value="oldest" {{ ($filters['sort'] ?? '') == 'oldest' ? 'selected' : '' }}>
                                    Terlama
                                </option>
                                <option value="title_asc"
                                    {{ ($filters['sort'] ?? '') == 'title_asc' ? 'selected' : '' }}>
                                    Judul (A-Z)</option>
                                <option value="title_desc"
                                    {{ ($filters['sort'] ?? '') == 'title_desc' ? 'selected' : '' }}>
                                    Judul (Z-A)</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Items Per Page -->
                    <div class="sm:col-span-2">
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Item per Halaman</label>
                        <div class="relative">
                            <select id="per_page" name="per_page" onchange="submitForm()"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none">
                                <option value="10" {{ ($filters['per_page'] ?? '10') == '10' ? 'selected' : '' }}>10
                                </option>
                                <option value="20" {{ ($filters['per_page'] ?? '') == '20' ? 'selected' : '' }}>20
                                </option>
                                <option value="50" {{ ($filters['per_page'] ?? '') == '50' ? 'selected' : '' }}>50
                                </option>
                                <option value="100" {{ ($filters['per_page'] ?? '') == '100' ? 'selected' : '' }}>100
                                </option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Clear Filters Button -->
                    <div class="sm:col-span-2 flex items-end">
                        <button type="button" onclick="clearFilters()"
                            class="w-full inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset Filter
                        </button>
                    </div>
                </div>

                <div class="hidden">
                    <button type="submit" id="submit-button" class="hidden">Filter</button>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="mt-8">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Questionnaires Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Kuesioner
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $totalQuestionnaires }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Questionnaires Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Kuesioner Aktif
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $activeQuestionnaires }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Responses Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Respons
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $totalResponses }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response Rate Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Tingkat Respons
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $overallResponseRate }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="mt-8 flex justify-end">
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                <button type="button" onclick="toggleView('grid')" id="grid-view-btn"
                    class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 active-view">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="sr-only">Grid view</span>
                </button>
                <button type="button" onclick="toggleView('list')" id="list-view-btn"
                    class="relative -ml-px inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span class="sr-only">List view</span>
                </button>
            </span>
        </div>

        <!-- Card Grid Layout -->
        <div class="mt-4" id="grid-view">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($questionnaires as $questionnaire)
                @include('components.dashboard.questionnaire-card', ['questionnaire' => $questionnaire])
                @empty
                <div class="col-span-full py-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        @if($activeTab === 'draft')
                        Tidak ada kuesioner draft
                        @elseif($activeTab === 'published')
                        Tidak ada kuesioner yang sedang dipublikasikan
                        @elseif($activeTab === 'closed')
                        Tidak ada kuesioner yang ditutup
                        @elseif($activeTab === 'template')
                        Tidak ada template kuesioner
                        @else
                        Tidak ada kuesioner
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($activeTab === 'draft')
                        Buat kuesioner baru dan simpan sebagai draft
                        @elseif($activeTab === 'published')
                        Publikasikan kuesioner agar dapat diisi oleh responden
                        @elseif($activeTab === 'closed')
                        Kuesioner yang telah berakhir akan muncul di sini
                        @elseif($activeTab === 'template')
                        Buat template kuesioner untuk digunakan kembali
                        @else
                        Mulai dengan membuat kuesioner baru
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('questionnaires.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Kuesioner Baru
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            @if($questionnaires->count() > 0)
            <div class="mt-6">
                <div class="pagination-container">
                    {{ $questionnaires->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>

        <!-- List View Layout -->
        <div class="mt-4 hidden" id="list-view">
            @if($questionnaires->count() > 0)
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="table-responsive">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kuesioner
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Respons
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dibuat
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($questionnaires as $questionnaire)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $questionnaire->title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($questionnaire->description, 60) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $questionnaire->status == 'published' ? 'bg-green-100 text-green-800' : 
                                       ($questionnaire->status == 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($questionnaire->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($questionnaire->start_date && $questionnaire->end_date)
                                    {{ \Carbon\Carbon::parse($questionnaire->start_date)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($questionnaire->end_date)->format('d/m/Y') }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $questionnaire->responses_count }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $questionnaire->response_rate }}% tingkat respons
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $questionnaire->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('questionnaires.show', $questionnaire->id) }}"
                                        class="text-indigo-600 hover:text-indigo-900 mr-2">Lihat</a>
                                    <a href="{{ route('questionnaires.edit', $questionnaire->id) }}"
                                        class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">
                <div class="pagination-container">
                    {{ $questionnaires->withQueryString()->links() }}
                </div>
            </div>
            @else
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        @if($activeTab === 'draft')
                        Tidak ada kuesioner draft
                        @elseif($activeTab === 'published')
                        Tidak ada kuesioner yang sedang dipublikasikan
                        @elseif($activeTab === 'closed')
                        Tidak ada kuesioner yang ditutup
                        @elseif($activeTab === 'template')
                        Tidak ada template kuesioner
                        @else
                        Tidak ada kuesioner
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($activeTab === 'draft')
                        Buat kuesioner baru dan simpan sebagai draft
                        @elseif($activeTab === 'published')
                        Publikasikan kuesioner agar dapat diisi oleh responden
                        @elseif($activeTab === 'closed')
                        Kuesioner yang telah berakhir akan muncul di sini
                        @elseif($activeTab === 'template')
                        Buat template kuesioner untuk digunakan kembali
                        @else
                        Mulai dengan membuat kuesioner baru
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('questionnaires.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Kuesioner Baru
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set initial view based on saved preference
    const savedView = localStorage.getItem('questionnaire_view') || 'grid';
    toggleView(savedView);
});

function toggleView(viewType) {
    const gridViewBtn = document.getElementById('grid-view-btn');
    const listViewBtn = document.getElementById('list-view-btn');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    if (viewType === 'grid') {
        gridViewBtn.classList.add('active-view', 'bg-gray-100');
        listViewBtn.classList.remove('active-view', 'bg-gray-100');
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        // Save preference
        localStorage.setItem('questionnaire_view', 'grid');
    } else {
        gridViewBtn.classList.remove('active-view', 'bg-gray-100');
        listViewBtn.classList.add('active-view', 'bg-gray-100');
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        // Save preference
        localStorage.setItem('questionnaire_view', 'list');
    }
}

function submitForm() {
    document.getElementById('submit-button').click();
}

function clearFilters() {
    // Reset all form fields
    document.getElementById('status-filter').value = '';
    document.getElementById('period').value = '';
    document.getElementById('is_template').value = '';
    document.getElementById('search').value = '';
    document.getElementById('sort').value = 'newest';

    // Maintain per_page value if it exists
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        // Keep the current value or reset to 10 if not set
        perPageSelect.value = perPageSelect.value || '10';
    }

    // Submit the form to apply the cleared filters
    submitForm();
}

function clearOtherFilters() {
    // Reset all filters except status
    document.getElementById('period').value = '';
    document.getElementById('search').value = '';
    document.getElementById('sort').value = 'newest';
}

function clearSearch() {
    document.getElementById('search').value = '';
    submitForm();
}
</script>
@endpush
@endsection