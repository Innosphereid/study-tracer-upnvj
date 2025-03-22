@props([
'type' => 'text',
'name',
'id' => null,
'label' => null,
'placeholder' => null,
'value' => null,
'required' => false,
'autofocus' => false,
'disabled' => false,
'error' => null,
'icon' => null
])

@php
$id = $id ?? $name;
$hasError = $error !== null;
@endphp

<div class="mb-4 animate-fade-in" style="animation-delay: calc(0.15s * {{ $loop->index ?? 1 }})">
    @if($label)
    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-700">
        {{ $label }} @if($required) <span class="text-red-500">*</span> @endif
    </label>
    @endif

    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
            <i class="{{ $icon }}"></i>
        </div>
        @endif

        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            placeholder="{{ $placeholder }}" @if($required) required @endif @if($autofocus) autofocus @endif
            @if($disabled) disabled @endif class="
                w-full px-4 py-2 border-b-2 form-input-animation 
                focus:outline-none 
                {{ $icon ? 'pl-10' : '' }}
                {{ $hasError 
                    ? 'border-red-300 focus:border-red-500 bg-red-50 text-red-900' 
                    : 'border-gray-300 focus:border-blue-600 bg-white text-gray-900' 
                }}
            " />

        @if($hasError)
        <div class="mt-1 text-sm text-red-600 animate-fade-in">
            {{ $error }}
        </div>
        @endif
    </div>
</div>