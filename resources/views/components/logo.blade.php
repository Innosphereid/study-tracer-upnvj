@props(['size' => 'md', 'class' => ''])

@php
$sizes = [
'sm' => 'h-8',
'md' => 'h-12',
'lg' => 'h-16',
'xl' => 'h-20',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-center justify-center {$class}"]) }}>
    <img src="{{ asset('logo-upnvj.png') }}" alt="UPN Veteran Jakarta Logo" class="{{ $sizeClass }} animate-fade-in"
        style="animation-delay: 0.2s">
</div>