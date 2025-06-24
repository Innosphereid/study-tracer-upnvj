@extends('layouts.dashboard')

@section('title', $questionnaire->title . ' - Results - TraceStudy UPNVJ')

@section('description', 'Results overview for ' . $questionnaire->title)

@section('page-title', $questionnaire->title . ' - Results')

@section('head')
<!-- Open Graph Meta Tags -->
<meta property="og:title" content="{{ $questionnaire->title }} - Results - TraceStudy UPNVJ">
<meta property="og:description" content="Results overview for {{ $questionnaire->title }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="TraceStudy UPNVJ">

<!-- Vue Results App -->
@vite('resources/js/questionnaire/results.js')
@endsection

@section('content')
<!-- Vue Results Mount Point -->
<div id="questionnaire-results" data-questionnaire="{{ json_encode($questionnaire) }}"
    data-statistics="{{ json_encode($statistics) }}" data-questionnaire-id="{{ $questionnaire->id }}">
</div>

<!-- Error message (hidden by default) -->
<div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4"
    style="display: none;">
    <strong class="font-bold">Error!</strong>
    <span class="block sm:inline">There was a problem loading the results. Please refresh the page or contact support if
        the issue persists.</span>
</div>
@endsection

@push('scripts')
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
@endpush