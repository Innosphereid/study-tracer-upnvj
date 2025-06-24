<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Preview Kuesioner - TraceStudy UPNVJ')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')

    <style>
    /* Base animation styles for micro-interactions */
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }

    .slide-up-enter-active,
    .slide-up-leave-active {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .slide-up-enter-from,
    .slide-up-leave-to {
        transform: translateY(10px);
        opacity: 0;
    }

    /* Elegant minimalist theme specific overrides */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: #334155;
    }

    .btn-primary {
        @apply inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white tracking-wide hover: bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150;
    }

    .btn-secondary {
        @apply inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 tracking-wide hover: bg-gray-50 active:bg-gray-100 focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150;
    }

    /* Add subtle hover effects to interactive elements */
    .hover-lift {
        transition: transform 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
    }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Preview Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('questionnaires.index') }}"
                            class="flex items-center hover:opacity-80 transition-opacity">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto mr-3">
                            <span class="text-lg font-semibold text-gray-900">Tracer Study UPNVJ</span>
                        </a>
                    </div>
                    <div class="flex items-center">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Mode Pratinjau
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-6">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} TraceStudy UPNVJ. All rights reserved.
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>

</html>