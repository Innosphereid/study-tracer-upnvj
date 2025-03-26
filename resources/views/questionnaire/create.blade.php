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

    // Cek apakah Vite dan JS sudah dimuat dengan benar
    console.log('Vite injected scripts:', document.querySelectorAll('script[type="module"]').length);
});
</script>
@endsection