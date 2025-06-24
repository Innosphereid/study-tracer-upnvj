{{-- 
Progress Bar Component

This component displays a percentage-based progress bar with color coding
based on the progress value.

@param float $percentage - The progress percentage (0-100)
@param string $height - Optional height of the progress bar (default: 'h-2.5')
@param bool $showLabel - Whether to show the percentage label (default: false)
--}}

@props(['percentage', 'height' => 'h-2.5', 'showLabel' => false])

<div class="w-full">
    @if($showLabel)
    <div class="flex justify-between items-center mb-1">
        <span class="text-xs font-medium text-gray-500">Progress</span>
        <span class="text-xs font-medium text-gray-500">{{ $percentage }}%</span>
    </div>
    @endif
    
    <div class="w-full bg-gray-200 rounded-full {{ $height }}">
        <div 
            class="{{ $percentage >= 70 ? 'bg-green-500' : ($percentage >= 30 ? 'bg-yellow-500' : 'bg-red-500') }} {{ $height }} rounded-full" 
            style="width: {{ $percentage }}%">
        </div>
    </div>
</div> 