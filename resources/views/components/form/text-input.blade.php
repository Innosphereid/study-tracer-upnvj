@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
'class' => 'w-full rounded-lg border-gray-300 pl-14 pr-6 py-3 shadow-sm focus:border-indigo-500 focus:ring
focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-300 ease-in-out text-base ' .
($errors->has($attributes->get('name')) ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''),
]) !!}>