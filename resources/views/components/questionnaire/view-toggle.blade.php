{{-- 
/**
 * Questionnaire View Toggle Component
 * 
 * This component provides buttons to toggle between grid and list views of questionnaires.
 * It works in conjunction with the view-toggle-scripts component to handle the toggle functionality.
 * 
 * @param string $initialView Initial view to display (grid or list). Default is 'grid'.
 */
--}}

@props(['initialView' => 'grid'])

<div class="flex justify-end">
    <span class="relative z-0 inline-flex shadow-sm rounded-md">
        <button type="button" onclick="toggleView('grid')" id="grid-view-btn"
            class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 {{ $initialView === 'grid' ? 'active-view' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="sr-only">Grid view</span>
        </button>
        <button type="button" onclick="toggleView('list')" id="list-view-btn"
            class="relative -ml-px inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 {{ $initialView === 'list' ? 'active-view' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span class="sr-only">List view</span>
        </button>
    </span>
</div>