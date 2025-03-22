<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'UPNVJ Study Tracer System')">
    <title>@yield('title', 'UPNVJ Study Tracer System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-between">
        <div class="flex items-center justify-center p-4 flex-grow">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="w-full text-center py-4 text-xs text-gray-500">
            <p>
                &copy; {{ date('Y') }} UPN Veteran Jakarta - Study Tracer System | Tim CDE UPN Veteran Jakarta
            </p>
        </footer>
    </div>
</body>

</html>