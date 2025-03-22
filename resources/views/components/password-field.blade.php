@props([
'name' => 'password',
'id' => 'password',
'label' => 'Password',
'placeholder' => 'Enter your password',
'value' => null,
'required' => true,
'autofocus' => false,
'disabled' => false,
'error' => null
])

<div class="mb-4 animate-fade-in" style="animation-delay: 0.3s">
    @if($label)
    <label for="{{ $id }}" class="block mb-2 text-sm font-medium text-gray-700">
        {{ $label }} @if($required) <span class="text-red-500">*</span> @endif
    </label>
    @endif

    <div class="relative" x-data="{ show: false }">
        <input :type="show ? 'text' : 'password'" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            placeholder="{{ $placeholder }}" @if($required) required @endif @if($autofocus) autofocus @endif
            @if($disabled) disabled @endif class="
                w-full px-4 py-2 pr-10 border-b-2 form-input-animation 
                focus:outline-none 
                {{ $error !== null 
                    ? 'border-red-300 focus:border-red-500 bg-red-50 text-red-900' 
                    : 'border-gray-300 focus:border-blue-600 bg-white text-gray-900' 
                }}
            " />

        <button type="button" @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors">
            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                </path>
            </svg>
            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                </path>
            </svg>
        </button>

        @if($error)
        <div class="mt-1 text-sm text-red-600 animate-fade-in">
            {{ $error }}
        </div>
        @endif
    </div>
</div>