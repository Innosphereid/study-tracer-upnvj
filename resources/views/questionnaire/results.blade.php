<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $questionnaire->title }} - Results - TraceStudy UPNVJ</title>
    <meta name="description" content="Results overview for {{ $questionnaire->title }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/questionnaire/results.js'])

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $questionnaire->title }} - Results - TraceStudy UPNVJ">
    <meta property="og:description" content="Results overview for {{ $questionnaire->title }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TraceStudy UPNVJ">
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <!-- App Wrapper -->
    <div id="app" class="container mx-auto py-8 px-4">
        <!-- Vue Results Mount Point -->
        <div id="questionnaire-results" data-questionnaire="{{ json_encode($questionnaire) }}"
            data-statistics="{{ json_encode($statistics) }}" data-questionnaire-id="{{ $questionnaire->id }}">
        </div>

        <!-- Fallback Error Handler -->
        <div id="error-message" style="display: none; margin-top: 2rem;"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <h2 class="font-bold text-xl mb-2">Terjadi kesalahan saat memuat hasil kuesioner</h2>
            <p class="mb-4">Mohon maaf atas ketidaknyamanan ini. Beberapa kemungkinan penyebabnya:</p>
            <ul class="list-disc pl-5 mb-4">
                <li>Ada masalah dengan JavaScript aplikasi</li>
                <li>Data hasil kuesioner mungkin tidak valid atau rusak</li>
                <li>Ada masalah dengan koneksi jaringan</li>
            </ul>
            <p class="mb-4">Silakan coba beberapa hal berikut:</p>
            <ul class="list-disc pl-5 mb-4">
                <li>Refresh halaman ini</li>
                <li>Bersihkan cache browser Anda</li>
                <li>Coba dengan browser yang berbeda</li>
            </ul>
            <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                onclick="window.location.reload()">
                Muat Ulang Halaman
            </button>
        </div>
    </div>

    <script>
    // Detect if app failed to load
    window.addEventListener('error', function(event) {
        console.error('Global error caught:', event.error);
        document.getElementById('error-message').style.display = 'block';
    });

    // Check if app mounted after 3 seconds
    setTimeout(function() {
        const appElement = document.querySelector('#questionnaire-results');
        if (appElement && appElement.children.length === 0) {
            console.error('Vue app failed to mount after timeout');
            document.getElementById('error-message').style.display = 'block';
        }
    }, 3000);

    // Make questionnaire data available in console for debugging
    try {
        const appElement = document.querySelector('#questionnaire-results');
        if (appElement) {
            window.questionnaireData = JSON.parse(appElement.dataset.questionnaire);
            window.statisticsData = JSON.parse(appElement.dataset.statistics);
            console.log('Debug data available in console as window.questionnaireData and window.statisticsData');
        }
    } catch (e) {
        console.error('Error parsing questionnaire data:', e);
    }
    </script>
</body>

</html>