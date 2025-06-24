{{-- 
/**
 * Questionnaire Grid View Component
 * 
 * This component displays questionnaires in a responsive grid layout.
 * It uses the questionnaire-card component to render each individual questionnaire card.
 * If no questionnaires are available, it displays an empty state with appropriate messaging.
 * 
 * @param \Illuminate\Support\Collection $questionnaires Collection of questionnaire models to display
 * @param string $activeTab The current active tab/filter (e.g., 'draft', 'published', 'closed', 'template')
 */
--}}

@props(['questionnaires', 'activeTab'])

<div id="grid-view">
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