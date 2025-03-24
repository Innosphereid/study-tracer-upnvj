@props([
'title' => 'Link',
'description' => null,
'icon' => null,
'route' => '#',
'buttonText' => 'Akses',
'color' => 'indigo',
])

@php
$iconMap = [
'document-add' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
',
'chart-bar' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
',
'download' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />',
'user-add' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />',
'clipboard-list' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
',
'academic-cap' => '
<path d="M12 14l9-5-9-5-9 5 9 5z" />
<path
    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
',
];

$colorMap = [
'indigo' => [
'bg' => 'bg-indigo-50',
'text' => 'text-indigo-700',
'button' => 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'
],
'blue' => [
'bg' => 'bg-blue-50',
'text' => 'text-blue-700',
'button' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'
],
'green' => [
'bg' => 'bg-green-50',
'text' => 'text-green-700',
'button' => 'bg-green-100 text-green-700 hover:bg-green-200'
],
'red' => [
'bg' => 'bg-red-50',
'text' => 'text-red-700',
'button' => 'bg-red-100 text-red-700 hover:bg-red-200'
],
'yellow' => [
'bg' => 'bg-yellow-50',
'text' => 'text-yellow-700',
'button' => 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
],
'purple' => [
'bg' => 'bg-purple-50',
'text' => 'text-purple-700',
'button' => 'bg-purple-100 text-purple-700 hover:bg-purple-200'
],
];

$colorClasses = $colorMap[$color] ?? $colorMap['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg']) }}>
    <div class="{{ $colorClasses['bg'] }} p-5">
        <div class="flex items-start">
            @if($icon && isset($iconMap[$icon]))
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 {{ $colorClasses['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $iconMap[$icon] !!}
                </svg>
            </div>
            @endif

            <div class="ml-4 flex-1">
                <h3 class="text-lg font-medium {{ $colorClasses['text'] }}">{{ $title }}</h3>

                @if($description)
                <p class="mt-1 text-sm {{ $colorClasses['text'] }} opacity-90">
                    {{ $description }}
                </p>
                @endif

                <div class="mt-3">
                    <a href="{{ $route }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md {{ $colorClasses['button'] }}">
                        {{ $buttonText }}
                        <svg class="ml-1 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>