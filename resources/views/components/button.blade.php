@props([
'type' => 'button',
'variant' => 'primary',
'size' => 'md',
'loading' => false,
'loadingText' => 'Processing...',
'icon' => null,
'iconPosition' => 'left',
'disabled' => false
])

@php
$baseClasses = "inline-flex items-center justify-center font-medium rounded focus:outline-none transition-all
btn-animation";

$variants = [
'primary' => "bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2",
'secondary' => "bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2",
'success' => "bg-green-600 text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2",
'danger' => "bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2",
'warning' => "bg-amber-500 text-white hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2",
];

$sizes = [
'sm' => "text-xs px-2.5 py-1.5",
'md' => "text-sm px-4 py-2",
'lg' => "text-base px-6 py-3",
'xl' => "text-lg px-8 py-4",
];

$variantClasses = $variants[$variant] ?? $variants['primary'];
$sizeClasses = $sizes[$size] ?? $sizes['md'];

$classes = "{$baseClasses} {$variantClasses} {$sizeClasses}";

if ($disabled || $loading) {
$classes .= " opacity-80 cursor-not-allowed";
}
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} {{ $disabled || $loading ? 'disabled' : '' }}
    x-data="{ loading: {{ $loading ? 'true' : 'false' }} }" @if($loading)
    x-init="setTimeout(() => loading = false, 2000)" @endif>
    <span x-show="!loading" class="flex items-center space-x-2">
        @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }} mr-2"></i>
        @endif

        <span>{{ $slot }}</span>

        @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ml-2"></i>
        @endif
    </span>

    <span x-show="loading" class="flex items-center space-x-2" style="display: none;">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span>{{ $loadingText }}</span>
    </span>
</button>