@extends('layouts.app')

@section('title', 'Edit Kuesioner - TraceStudy UPNVJ')

@section('styles')
@vite(['resources/js/questionnaire/index.js'])
@endsection

@section('content')
<div class="h-screen bg-gray-50 flex flex-col">
    <!-- Vue Builder Mount Point with existing questionnaire data -->
    <div id="questionnaire-builder" data-questionnaire="{{ json_encode($questionnaire) }}"></div>
</div>
@endsection

@section('scripts')
<script>
// Check if we need to open publish modal on page load (redirected from preview)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('publish') === 'true') {
        // Wait for Vue to initialize, then trigger publish action
        setTimeout(() => {
            const publishButton = document.querySelector('button[data-action="publish"]');
            if (publishButton) {
                publishButton.click();
            }
        }, 500);
    }
});
</script>
@endsection