@if (session('success'))
<x-alert type="success" :message="session('success')" />
@endif

@if (session('error'))
<x-alert type="error" :message="session('error')" />
@endif

@if (session('info'))
<x-alert type="info" :message="session('info')" />
@endif

@if (session('warning'))
<x-alert type="warning" :message="session('warning')" />
@endif

@if ($errors->any())
<div class="border rounded-lg p-4 mb-4 bg-red-50 text-red-800 border-red-200">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium">There were {{ count($errors) }} {{ Str::plural('error', count($errors)) }}
                with your submission</h3>
            <div class="mt-2 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif