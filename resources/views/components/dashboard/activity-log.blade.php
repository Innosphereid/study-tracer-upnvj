@props([
'activities' => [],
'title' => 'Aktivitas Terbaru',
'limit' => 5,
'showTimestamp' => true,
'showUser' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm rounded-lg']) }}>
    <div class="p-5">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $title }}</h3>

        <div class="space-y-4">
            @forelse($activities as $activity)
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100 text-indigo-600">
                        @php
                        $iconMap = [
                        'login' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        ',
                        'create' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />',
                        'update' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        ',
                        'delete' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        ',
                        'view' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        ',
                        'download' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />',
                        'default' => '
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                        ];

                        $icon = $activity->action ?? 'default';
                        $iconPath = $iconMap[$icon] ?? $iconMap['default'];
                        @endphp

                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $iconPath !!}
                        </svg>
                    </div>
                </div>

                <div class="ml-4 flex-1">
                    <div class="text-sm text-gray-900">
                        {{ $activity->description ?? 'Aktivitas tidak diketahui' }}
                    </div>

                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                        <div class="flex space-x-1">
                            @if($showUser && isset($activity->user))
                            <span>{{ $activity->user->name ?? 'Unknown' }}</span>
                            <span>&middot;</span>
                            @endif

                            @if($showTimestamp && isset($activity->created_at))
                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                            @endif
                        </div>

                        @if(isset($activity->link))
                        <a href="{{ $activity->link }}" class="text-indigo-600 hover:text-indigo-900">
                            Lihat detail
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4">
                <p class="text-gray-500 text-sm">Belum ada aktivitas.</p>
            </div>
            @endforelse

            @if(count($activities) > 0 && isset($viewAllRoute))
            <div class="mt-6 text-center">
                <a href="{{ $viewAllRoute }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Lihat semua aktivitas
                </a>
            </div>
            @endif
        </div>
    </div>
</div>