{{-- 
/**
 * Questionnaire List View Component
 * 
 * This component displays questionnaires in a tabular list layout with sorting and pagination.
 * It shows details like title, status, period, responses, and creation date.
 * If no questionnaires are available, it displays an empty state with appropriate messaging.
 * 
 * @param \Illuminate\Support\Collection $questionnaires Collection of questionnaire models to display
 * @param string $activeTab The current active tab/filter (e.g., 'draft', 'published', 'closed', 'template')
 */
--}}

@props(['questionnaires', 'activeTab'])

<div id="list-view" class="hidden">
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