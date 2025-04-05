{{-- 
/**
 * Questionnaire Filters Component
 * 
 * This component renders a comprehensive filtering interface for questionnaires.
 * It includes search functionality, status filtering, period filtering,
 * template type filtering, sorting options, and items per page selection.
 * 
 * @param array $filters Current filter values from the request
 */
--}}

@props(['filters'])

<div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
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