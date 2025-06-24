/** * @component AlumniQuestionnaireWrapper * @description Main container
component for the alumni questionnaire * This component serves as the entry
point and orchestrates the questionnaire flow */
<template>
    <div class="alumni-questionnaire-app min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <questionnaire-header
            :title="questionnaire.title"
            :subtitle="'TraceStudy Alumni UPNVJ'"
        />

        <!-- Main Content -->
        <main class="flex-1 mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
            <loading-spinner v-if="isLoading" />

            <div v-else>
                <!-- Welcome Screen -->
                <welcome-screen
                    v-if="currentStep === 'welcome'"
                    :title="welcomeScreenTitle"
                    :subtitle="welcomeScreenSubtitle"
                    :description="welcomeScreenDescription"
                    @start="startQuestionnaire"
                />

                <!-- Questionnaire Content -->
                <questionnaire-content
                    v-else-if="currentStep === 'questions'"
                    :current-section="currentSection"
                    :current-section-index="currentSectionIndex"
                    :total-sections="totalSections"
                    :progress="progress"
                    :show-progress-bar="questionnaire.showProgressBar"
                    :show-page-numbers="questionnaire.showPageNumbers"
                    :answers="answers"
                    :errors="errors"
                    :is-submitting="isSubmitting"
                    @validation="handleValidationEvent"
                    @previous-section="previousSection"
                    @next-section="nextSection"
                    @submit-questionnaire="submitQuestionnaire"
                />

                <!-- Thank You Screen -->
                <thank-you-screen
                    v-else-if="currentStep === 'thankYou'"
                    :title="thankYouScreenTitle"
                    :description="thankYouScreenDescription"
                    :show-home-button="true"
                    :home-url="'/'"
                />
            </div>
        </main>

        <!-- Footer -->
        <questionnaire-footer />
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import QuestionnaireHeader from "./ui/QuestionnaireHeader.vue";
import QuestionnaireFooter from "./ui/QuestionnaireFooter.vue";
import WelcomeScreen from "./screens/WelcomeScreen.vue";
import ThankYouScreen from "./screens/ThankYouScreen.vue";
import LoadingSpinner from "./features/LoadingSpinner.vue";
import QuestionnaireContent from "./features/QuestionnaireContent.vue";

// Import composables
import useAlumniQuestionnaire from "../../composables/useAlumniQuestionnaire";

/**
 * Component props
 */
const props = defineProps({
    /**
     * Questionnaire data object
     */
    questionnaire: {
        type: Object,
        required: true,
    },

    /**
     * Whether the questionnaire is in preview mode
     */
    isPreview: {
        type: Boolean,
        default: false,
    },
});

// Initialize the questionnaire state and logic
const {
    // State
    isLoading,
    isSubmitting,
    currentStep,
    currentSectionIndex,
    answers,
    errors,
    validationState,

    // Computed
    totalSections,
    currentSection,
    progress,
    welcomeScreenTitle,
    welcomeScreenSubtitle,
    welcomeScreenDescription,
    thankYouScreenTitle,
    thankYouScreenDescription,

    // Methods
    startQuestionnaire,
    previousSection,
    nextSection,
    handleValidation,
    submitQuestionnaire,
    initializeQuestionnaire,
} = useAlumniQuestionnaire({
    questionnaire: props.questionnaire,
    isPreview: props.isPreview,
});

/**
 * Handles validation events from the questionnaire content component
 *
 * @param {Object} event - The validation event
 * @param {Object} event.validationResult - The validation result
 * @param {String} event.questionId - The ID of the question
 */
const handleValidationEvent = ({ validationResult, questionId }) => {
    handleValidation(validationResult, questionId);
};

// Initialize the questionnaire on mount
onMounted(() => {
    initializeQuestionnaire();
});
</script>
