@props([
'title' => 'Statistik',
'value' => '0',
'icon' => null,
'trend' => null,
'trendValue' => null,
'trendLabel' => 'dari bulan lalu',
'iconBackground' => 'bg-indigo-500',
'withTrend' => true,
])

@php
$iconMap = [
'users' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
',
'document' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
',
'chart-bar' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
',
'academic-cap' => '
<path d="M12 14l9-5-9-5-9 5 9 5z" />
<path
    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
',
'clipboard-check' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
',
'shield-check' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
',
];

$trendIconColor = $trend === 'up' ? 'text-green-500' : 'text-red-500';
$trendIcon = $trend === 'up'
? '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />'
: '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm rounded-lg']) }}>
    <div class="p-5">
        <div class="flex items-center">
            @if($icon)
            <div class="{{ $iconBackground }} rounded-md p-3 mr-4">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $iconMap[$icon] ?? '' !!}
                </svg>
            </div>
            @endif

            <div>
                <p class="text-sm font-medium text-gray-500 truncate">
                    {{ $title }}
                </p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ $value }}
                </p>

                @if($withTrend && $trend && $trendValue)
                <div class="flex items-center mt-2">
                    <svg class="w-4 h-4 {{ $trendIconColor }} mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        {!! $trendIcon !!}
                    </svg>
                    <span class="text-sm {{ $trendIconColor }} font-medium">
                        {{ $trendValue }}
                    </span>
                    <span class="text-sm text-gray-500 ml-1">
                        {{ $trendLabel }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>