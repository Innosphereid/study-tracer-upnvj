@props(['questionnaire', 'class' => ''])

<div
    class="bg-white rounded-lg shadow transition duration-300 ease-in-out hover:shadow-md hover:-translate-y-1 {{ $class }}">
    {{-- Card Header --}}
    <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-start">
            <div class="flex flex-col">
                <h3 class="text-lg font-medium text-gray-900 truncate max-w-xs">
                    {{ $questionnaire->title }}
                </h3>
                <p class="text-sm text-gray-500 mt-1 truncate max-w-xs">
                    /kuesioner/{{ $questionnaire->slug }}
                </p>
            </div>
            <div class="flex space-x-2">
                {{-- Status Badge --}}
                @if($questionnaire->status === 'published' && $questionnaire->isActive())
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Aktif
                </span>
                @elseif($questionnaire->status === 'draft')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Draft
                </span>
                @else
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Nonaktif
                </span>
                @endif

                {{-- Template Indicator --}}
                @if($questionnaire->is_template)
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="-ml-0.5 mr-1.5 h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Template
                </span>
                @endif
            </div>
        </div>

        {{-- Date Range --}}
        <div class="mt-3 flex items-center text-sm text-gray-500">
            <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            @if($questionnaire->start_date && $questionnaire->end_date)
            {{ \Carbon\Carbon::parse($questionnaire->start_date)->isoFormat('D MMM Y') }} -
            {{ \Carbon\Carbon::parse($questionnaire->end_date)->isoFormat('D MMM Y') }}
            @elseif($questionnaire->start_date)
            Mulai {{ \Carbon\Carbon::parse($questionnaire->start_date)->isoFormat('D MMM Y') }}
            @elseif($questionnaire->end_date)
            Sampai {{ \Carbon\Carbon::parse($questionnaire->end_date)->isoFormat('D MMM Y') }}
            @else
            Tidak ada batasan waktu
            @endif
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-4">
        <div class="flex space-x-4 justify-between">
            {{-- Sections --}}
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="text-sm text-gray-600">{{ $questionnaire->sections_count }} seksi</span>
            </div>

            {{-- Questions --}}
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-600">{{ $questionnaire->questions_count }} pertanyaan</span>
            </div>

            {{-- Responses --}}
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                <span class="text-sm text-gray-600">{{ $questionnaire->responses_count }} respons</span>
            </div>
        </div>

        {{-- Response Rate Progress Bar --}}
        <div class="mt-4">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-medium text-gray-500">Tingkat Respons</span>
                <span class="text-xs font-medium text-gray-500">{{ $questionnaire->response_rate }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                @php
                $rate = $questionnaire->response_rate;
                $bgColor = 'bg-red-500';
                if ($rate >= 70) $bgColor = 'bg-green-500';
                elseif ($rate >= 30) $bgColor = 'bg-yellow-500';
                @endphp
                <div class="{{ $bgColor }} h-2.5 rounded-full" style="width: {{ $rate }}%"></div>
            </div>
        </div>
    </div>

    {{-- Card Footer --}}
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex justify-between dropdown-container">
            <x-dashboard.questionnaire-actions :questionnaire="$questionnaire" />
        </div>
    </div>
</div>

{{-- The Toast and JavaScript function for sharing will be included in the questionnaire-actions component --}}