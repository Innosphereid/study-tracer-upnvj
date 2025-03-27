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
    console.log('Edit page loaded');
    console.log('CSRF token exists:', !!document.querySelector('meta[name="csrf-token"]'));
    console.log('CSRF token value:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    
    // Log data questionnaire
    const element = document.getElementById('questionnaire-builder');
    if (element && element.dataset.questionnaire) {
        try {
            const data = JSON.parse(element.dataset.questionnaire);
            console.log('Questionnaire data loaded:', data);
            console.log('ID:', data.id);
            console.log('ID type:', typeof data.id);
            console.log('Status:', data.status);
            
            // Trigger helpful error message if startsWith would be problematic
            if (data.id && typeof data.id !== 'string') {
                console.warn('ID is not a string (' + typeof data.id + '), startsWith() method will cause errors if used on this value');
            }
        } catch (e) {
            console.error('Error parsing questionnaire data:', e);
        }
    }
    
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