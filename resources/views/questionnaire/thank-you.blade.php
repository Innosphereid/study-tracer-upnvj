<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terima Kasih - {{ $questionnaire->title }} - TraceStudy UPNVJ</title>
    <meta name="description" content="Terima kasih telah menyelesaikan kuesioner {{ $questionnaire->title }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Terima Kasih - {{ $questionnaire->title }} - TraceStudy UPNVJ">
    <meta property="og:description" content="Terima kasih telah menyelesaikan kuesioner {{ $questionnaire->title }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TraceStudy UPNVJ">
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <h1 class="text-xl font-semibold text-gray-900">TraceStudy UPNVJ</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-8">
                    <div class="text-center">
                        <!-- Success Icon -->
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                            <svg class="h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        
                        <!-- Thank You Title -->
                        <h2 class="mt-6 text-3xl font-bold text-gray-900">
                            Terima Kasih!
                        </h2>
                        
                        <!-- Description -->
                        <p class="mt-3 text-lg text-gray-600">
                            Jawaban Anda telah kami terima. Terima kasih atas partisipasi Anda dalam kuesioner ini.
                        </p>
                        
                        @if(session('success'))
                        <div class="mt-4 p-4 bg-green-50 rounded-lg text-green-800">
                            {{ session('success') }}
                        </div>
                        @endif
                        
                        <!-- Back Home Button -->
                        <div class="mt-8">
                            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} TraceStudy UPNVJ. Developed by TIM HLP PTIK
                </p>
            </div>
        </footer>
    </div>

    <!-- Footer Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mark questionnaire as submitted in browser storage
        const formKey = 'questionnaire_{{ $questionnaire->id }}_submitted';
        localStorage.setItem(formKey, 'true');
    });
    </script>
</body>

</html> 