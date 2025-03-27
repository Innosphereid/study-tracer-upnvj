@extends('layouts.dashboard')

@section('title', 'Buat Kuesioner Baru - TraceStudy UPNVJ')

@section('content')
<div class="h-screen flex flex-col bg-gray-50">
    <!-- Vue Builder Mount Point -->
    <div id="questionnaire-builder" data-questionnaire="{{ json_encode($initialData ?? new stdClass()) }}"
        class="flex flex-col h-full"></div>
</div>
@endsection

@section('styles')
@vite(['resources/css/app.css'])
@endsection

@section('scripts')
@vite(['resources/js/questionnaire/index.js'])
<script>
// Tambahkan script debugging untuk membantu troubleshooting
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
    console.log('Builder element exists:', !!document.getElementById('questionnaire-builder'));
    console.log('CSRF token exists:', !!document.querySelector('meta[name="csrf-token"]'));
    console.log('CSRF token value:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    
    // Log data questionnaire
    const element = document.getElementById('questionnaire-builder');
    if (element && element.dataset.questionnaire) {
        try {
            const data = JSON.parse(element.dataset.questionnaire);
            console.log('Initial data loaded:', data);
            console.log('ID type:', typeof data.id);
        } catch (e) {
            console.error('Error parsing questionnaire data:', e);
        }
    }

    // Cek apakah Vite dan JS sudah dimuat dengan benar
    console.log('Vite injected scripts:', document.querySelectorAll('script[type="module"]').length);
});
</script>
@endsection