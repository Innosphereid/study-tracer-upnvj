/** * @component QuestionnaireContent * @description Component that displays and
manages the questionnaire questions and navigation */
<template>
    <div class="questionnaire-content space-y-8">
        <!-- Progress Bar -->
        <progress-bar
            v-if="showProgressBar"
            :progress="progress"
            :current-section="currentSectionIndex + 1"
            :total-sections="totalSections"
            :show-section-numbers="showPageNumbers"
        />

        <!-- Current Section -->
        <section-container
            v-if="currentSection"
            :title="currentSection.title"
            :description="currentSection.description"
        >
            <!-- Questions -->
            <template
                v-for="(question, index) in currentSection.questions"
                :key="question.id"
            >
                <!-- Use dynamic component to load the correct question type -->
                <component
                    :is="getQuestionComponent(question.type)"
                    :question="question"
                    v-model="answers[question.id]"
                    :error="errors[question.id]"
                    @validate="handleValidation($event, question.id)"
                />
            </template>
        </section-container>

        <!-- Navigation -->
        <questionnaire-navigation
            :show-previous="currentSectionIndex > 0"
            :show-next="currentSectionIndex < totalSections - 1"
            :show-submit="currentSectionIndex === totalSections - 1"
            :is-submitting="isSubmitting"
            @previous="previousSection"
            @next="nextSection"
            @submit="submitQuestionnaire"
        />
    </div>
</template>

<script setup>
import ProgressBar from "../ui/ProgressBar.vue";
import SectionContainer from "../ui/SectionContainer.vue";
import QuestionnaireNavigation from "../ui/QuestionnaireNavigation.vue";
import useQuestionComponents from "../../../composables/useQuestionComponents";

const props = defineProps({
    /**
     * The current section of the questionnaire
     */
    currentSection: {
        type: Object,
        required: true,
    },

    /**
     * The current section index
     */
    currentSectionIndex: {
        type: Number,
        required: true,
    },

    /**
     * The total number of sections
     */
    totalSections: {
        type: Number,
        required: true,
    },

    /**
     * The current progress percentage
     */
    progress: {
        type: Number,
        required: true,
    },

    /**
     * Whether to show the progress bar
     */
    showProgressBar: {
        type: Boolean,
        default: true,
    },

    /**
     * Whether to show page numbers
     */
    showPageNumbers: {
        type: Boolean,
        default: true,
    },

    /**
     * The answers object
     */
    answers: {
        type: Object,
        required: true,
    },

    /**
     * The errors object
     */
    errors: {
        type: Object,
        required: true,
    },

    /**
     * Whether the questionnaire is being submitted
     */
    isSubmitting: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "validation",
    "previous-section",
    "next-section",
    "submit-questionnaire",
]);

// Setup component mappings
const { getQuestionComponent } = useQuestionComponents({
    debug: window.location.href.includes("debug=true"),
});

/**
 * Handles validation events from question components
 *
 * @param {Object} validationResult - The validation result
 * @param {String} questionId - The ID of the question
 */
const handleValidation = (validationResult, questionId) => {
    emit("validation", { validationResult, questionId });
};

/**
 * Navigate to the previous section
 */
const previousSection = () => {
    emit("previous-section");
};

/**
 * Navigate to the next section
 */
const nextSection = () => {
    emit("next-section");
};

/**
 * Submit the questionnaire
 */
const submitQuestionnaire = () => {
    emit("submit-questionnaire");
};
</script>

<style scoped>
.questionnaire-content {
    transition: opacity 0.3s ease;
}
</style>
