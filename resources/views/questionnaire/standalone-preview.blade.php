@extends('layouts.questionnaire.preview')

@section('title', 'Pratinjau Kuesioner Dasar - TraceStudy UPNVJ')

@section('styles')
@vite(['resources/js/questionnaire/index.js'])
<style>
    /* Preview-specific animations and styles */
    .question-enter-active, .question-leave-active {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .question-enter-from, .question-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }
    
    /* Progress bar animation */
    .progress-bar-animated {
        background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
        animation: progress-bar-animation 1s linear infinite;
    }
    
    @keyframes progress-bar-animation {
        0% {
            background-position: 1rem 0;
        }
        100% {
            background-position: 0 0;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <!-- Preview Instructions Banner -->
        <div class="bg-blue-50 p-4 border-b border-blue-100">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        Ini adalah tampilan pratinjau kuesioner demo. Anda dapat melihat bagaimana kuesioner akan terlihat bagi responden.
                    </p>
                    <p class="mt-3 text-sm md:mt-0 md:ml-6">
                        <a href="{{ route('questionnaires.index') }}" class="whitespace-nowrap font-medium text-blue-600 hover:text-blue-500 transition ease-in-out duration-150">
                            Lihat daftar kuesioner <span aria-hidden="true">&rarr;</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        @if(isset($error))
        <!-- Error Message -->
        <div class="bg-red-50 p-4 border-b border-red-100">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        {{ $error }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Standalone Vue Preview Mount Point -->
        <div id="standalone-preview" data-questionnaire="{{ json_encode($questionnaire) }}" class="p-0"></div>
    </div>

    <!-- Preview Actions -->
    <div class="mt-10 flex justify-between items-center">
        <a href="{{ route('questionnaires.edit', $questionnaire['id']) }}" class="btn-primary hover-lift">
            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Edit Kuesioner
        </a>

        <a href="{{ route('questionnaires.create') }}" class="btn-primary hover-lift">
            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Buat Kuesioner Baru
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Standalone preview page initialized');
    });
</script>
@endpush 