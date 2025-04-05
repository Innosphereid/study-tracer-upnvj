{{-- 
/**
 * Questionnaire Page Header Component
 * 
 * This component renders the page header section with title, description, and action button.
 * It displays a title, a descriptive text below it, and a button to create a new questionnaire.
 * 
 * @param string $title The page title text. Default: 'Daftar Kuesioner'
 * @param string $description The page description text. Default: 'Mengelola dan melacak semua kuesioner dalam sistem TraceStudy UPNVJ.'
 */
--}}

@props(['title' => 'Daftar Kuesioner', 'description' => 'Mengelola dan melacak semua kuesioner dalam sistem TraceStudy UPNVJ.'])

<div class="md:flex md:items-center md:justify-between">
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            {{ $title }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $description }}
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