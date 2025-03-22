<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="@yield('meta_description', 'Portal login resmi untuk sistem pengelolaan Study Tracer UPN Veteran Jakarta. Dikhususkan bagi admin dan superadmin Tim CDE.')">
    <title>@yield('title', 'Login - UPNVJ Study Tracer | Portal Admin Tim CDE')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite CSS -->
    @vite('resources/css/app.css')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

<body
    class="font-sans antialiased bg-gradient-to-tr from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4 md:p-8">
    <!-- Background Pattern - Subtle Grid with Animation -->
    <div class="fixed inset-0 z-0 opacity-5 pointer-events-none" aria-hidden="true">
        <div class="absolute inset-0 bg-repeat"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.2\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')">
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 w-full max-w-md">
        @yield('content')

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-xs">
            <p>&copy; {{ date('Y') }} UPN Veteran Jakarta. All rights reserved.</p>
            <p class="mt-1">Study Tracer Management System v1.0</p>
        </div>
    </div>

    <!-- Vite JS -->
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>