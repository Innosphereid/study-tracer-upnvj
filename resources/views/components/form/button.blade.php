@props(['type' => 'button'])

<button type="{{ $type }}" {{ $attributes->merge([
    'class' => 'flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition-all duration-300 ease-in-out transform hover:scale-[1.02] active:scale-[0.98]',
]) }}>
    {{ $slot }}
</button>