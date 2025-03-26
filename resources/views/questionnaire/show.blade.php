<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $questionnaire->title }} - TraceStudy UPNVJ</title>
    <meta name="description" content="{{ $questionnaire->description }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/questionnaire/index.js'])

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $questionnaire->title }} - TraceStudy UPNVJ">
    <meta property="og:description" content="{{ $questionnaire->description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TraceStudy UPNVJ">
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50">
    <!-- Vue Form Mount Point -->
    <div id="questionnaire-form" data-questionnaire="{{ json_encode($questionnaire) }}"></div>

    @if($questionnaire->requires_login && !auth()->check())
    <script>
    // Redirect to login if questionnaire requires authentication
    window.location.href = "{{ route('login', ['redirect' => url()->current()]) }}";
    </script>
    @endif

    <!-- Footer Scripts -->
    <script>
    // Check if form has been submitted
    const checkSubmission = () => {
        const formKey = 'questionnaire_{{ $questionnaire->id }}_submitted';
        return localStorage.getItem(formKey) === 'true';
    };

    // Mark as submitted
    const markAsSubmitted = () => {
        const formKey = 'questionnaire_{{ $questionnaire->id }}_submitted';
        localStorage.setItem(formKey, 'true');
    };

    // Listen for submission event from Vue component
    document.addEventListener('questionnaire-submitted', () => {
        markAsSubmitted();
    });

    // Show warning if already submitted
    if (checkSubmission()) {
        console.log('This questionnaire has already been submitted.');
        // Optionally show a notification or disable form
    }
    </script>
</body>

</html>