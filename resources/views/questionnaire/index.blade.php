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

<!-- Date Selection Modal Component (Shared Across All Questionnaires) -->
<x-questionnaire.date-selection-modal />

@push('scripts')
<!-- View Toggle Scripts -->
<x-questionnaire.view-toggle-scripts />

<!-- Date Selection Modal Scripts -->
<script>
/**
 * Opens the date selection modal and sets the current questionnaire ID and form URL
 * 
 * @param {string} questionnaireId - ID of the questionnaire to be published
 * @param {string} formUrl - URL to redirect after successful publishing
 * @returns {void}
 */
function openDateSelectionModal(questionnaireId, formUrl) {
    // Set default start date to today
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const todayFormatted = `${year}-${month}-${day}`;
    
    // Get modal elements
    const modal = document.getElementById('global-date-selection-modal');
    const startDateInput = document.getElementById('modal-start-date');
    const currentQuestionnaireIdInput = document.getElementById('current-questionnaire-id');
    const currentFormUrlInput = document.getElementById('current-form-url');
    
    // Set default values
    if (startDateInput && !startDateInput.value) {
        startDateInput.value = todayFormatted;
    }
    
    // Store current questionnaire ID and form URL
    if (currentQuestionnaireIdInput) {
        currentQuestionnaireIdInput.value = questionnaireId;
    }
    
    if (currentFormUrlInput) {
        currentFormUrlInput.value = formUrl;
    }
    
    // Show the modal
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); // Prevent background scrolling
    }
}

/**
 * Set up event listeners for the date selection modal
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('global-date-selection-modal');
    const modalBackdrop = document.getElementById('modal-backdrop');
    const confirmBtn = document.getElementById('confirm-date-selection-btn');
    const cancelBtn = document.getElementById('cancel-date-selection-btn');
    
    // Close modal when clicking backdrop
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Close modal when clicking cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Handle confirm button click
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const questionnaireId = document.getElementById('current-questionnaire-id').value;
            const formUrl = document.getElementById('current-form-url').value;
            const startDate = document.getElementById('modal-start-date').value;
            const endDate = document.getElementById('modal-end-date').value;
            
            // Transfer dates to hidden form inputs
            const formStartDateInput = document.getElementById(`start-date-${questionnaireId}`);
            const formEndDateInput = document.getElementById(`end-date-${questionnaireId}`);
            
            if (formStartDateInput) {
                formStartDateInput.value = startDate;
            }
            
            if (formEndDateInput) {
                formEndDateInput.value = endDate;
            }
            
            // Close modal
            closeModal();
            
            // Submit the form
            handlePublish(new Event('click'), questionnaireId, formUrl);
        });
    }
    
    /**
     * Closes the date selection modal
     */
    function closeModal() {
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
});
</script>
@endpush
@endsection