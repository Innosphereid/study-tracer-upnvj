@extends('layouts.dashboard')

@section('title', 'Daftar Kuesioner - TraceStudy UPNVJ')

@section('styles')
<x-questionnaire.styles />
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <x-questionnaire.page-header />

        <!-- Filter and Search Container -->
        <div class="mt-6">
            <x-questionnaire.filters :filters="$filters" />
        </div>

        <!-- Stats Cards -->
        <div class="mt-8">
            <x-questionnaire.stats :totalQuestionnaires="$totalQuestionnaires"
                :activeQuestionnaires="$activeQuestionnaires" :totalResponses="$totalResponses"
                :overallResponseRate="$overallResponseRate" />
        </div>

        <!-- View Toggle -->
        <div class="mt-8">
            <x-questionnaire.view-toggle />
        </div>

        <!-- Content Views -->
        <div class="mt-4">
            <x-questionnaire.grid-view :questionnaires="$questionnaires" :activeTab="$activeTab" />
            <x-questionnaire.list-view :questionnaires="$questionnaires" :activeTab="$activeTab" />
        </div>
    </div>
</div>

@push('scripts')
<x-questionnaire.view-toggle-scripts />
@endpush
@endsection