@extends('layouts.app')

@section('title', 'Pratinjau Kuesioner - TraceStudy UPNVJ')

@section('styles')
@vite(['resources/js/questionnaire/index.js'])
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Vue Preview Mount Point -->
    <div id="questionnaire-preview" data-questionnaire="{{ json_encode($questionnaire) }}"></div>
</div>
@endsection