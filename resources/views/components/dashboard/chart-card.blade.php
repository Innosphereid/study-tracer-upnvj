@props([
'title' => 'Chart',
'subtitle' => null,
'id' => 'chart-' . uniqid(),
'height' => '240px',
])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm rounded-lg']) }}>
    <div class="p-5">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                <p class="text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            <div>
                {{ $actions ?? '' }}
            </div>
        </div>

        <div id="{{ $id }}-container" style="height: {{ $height }};">
            {{ $slot }}
        </div>
    </div>
</div>