@props(['status'])

@if ($status)
<x-alert type="info" class="mb-4">
    {{ $status }}
</x-alert>
@endif