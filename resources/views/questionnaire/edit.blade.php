{{-- 
/**
 * Edit Questionnaire View
 * 
 * This view serves as the entry point for the questionnaire edit page.
 * It mounts the Vue.js Builder component and passes the questionnaire data
 * through the data-questionnaire attribute.
 * 
 * The Builder component is the same one used in the create page, but it
 * will initialize with the existing questionnaire data instead of starting empty.
 * 
 * @see resources/js/questionnaire/pages/Builder.vue
 * @see resources/js/questionnaire/store/questionnaire.js
 * @see resources/js/questionnaire/composables/useQuestionnaire.js
 */
--}}

@extends('layouts.dashboard')

@section('title', 'Edit Kuesioner - TraceStudy UPNVJ')

@section('content')
<div class="h-screen flex flex-col bg-gray-50">
    <!-- Vue Builder Mount Point - The same component used in create.blade.php -->
    <div id="questionnaire-builder" data-questionnaire="{{ json_encode($questionnaire) }}" class="flex flex-col h-full">
    </div>
</div>
@endsection

@section('styles')
@vite(['resources/css/app.css'])
@endsection

@section('scripts')
@vite(['resources/js/questionnaire/index.js'])
<script>
/**
 * Debugging script for the questionnaire builder
 * 
 * This script helps troubleshoot issues with the questionnaire builder
 * by logging important information to the console:
 * - Whether the builder element exists
 * - Whether the CSRF token exists and its value
 * - The parsed questionnaire data
 * - Debug information about sections and questions
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit page loaded');
    console.log('Builder element exists:', !!document.getElementById('questionnaire-builder'));
    console.log('CSRF token exists:', !!document.querySelector('meta[name="csrf-token"]'));
    console.log('CSRF token value:', document.querySelector('meta[name="csrf-token"]')?.getAttribute(
        'content'));

    // Log data questionnaire
    const element = document.getElementById('questionnaire-builder');
    if (element && element.dataset.questionnaire) {
        try {
            const data = JSON.parse(element.dataset.questionnaire);
            console.log('Questionnaire data loaded:', data);
            console.log('ID:', data.id);
            console.log('ID type:', typeof data.id);
            console.log('Status:', data.status);

            // Log detailed section and question information
            if (data.sections && Array.isArray(data.sections)) {
                console.log(`Found ${data.sections.length} sections in questionnaire data`);

                data.sections.forEach((section, sIndex) => {
                    console.log(`Section #${sIndex + 1}:`, {
                        id: section.id,
                        title: section.title,
                        questions_count: section.questions?.length || 0
                    });

                    if (section.questions && Array.isArray(section.questions)) {
                        section.questions.forEach((question, qIndex) => {
                            console.log(`- Question #${qIndex + 1}:`, {
                                id: question.id,
                                type: question.type,
                                text: question.text,
                                options_count: question.options?.length || 0
                            });
                        });
                    } else {
                        console.warn(
                            `Section #${sIndex + 1} has no questions or questions is not an array`);
                    }
                });
            } else {
                console.warn('No sections found in questionnaire data or sections is not an array');
            }

            // Trigger helpful error message if startsWith would be problematic
            if (data.id && typeof data.id !== 'string') {
                console.warn('ID is not a string (' + typeof data.id +
                    '), startsWith() method will cause errors if used on this value');
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