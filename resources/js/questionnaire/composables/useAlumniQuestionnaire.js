/**
 * @fileoverview Composable for managing alumni questionnaire state and logic
 *
 * This composable serves as the main entry point for questionnaire functionality.
 * It orchestrates other specialized composables to manage different aspects
 * of the questionnaire.
 */

import { onMounted } from "vue";
import useQuestionnaireState from "./useQuestionnaireState";
import useQuestionnaireScreens from "./useQuestionnaireScreens";
import useQuestionnaireValidation from "./useQuestionnaireValidation";
import useQuestionnaireNavigation from "./useQuestionnaireNavigation";
import useQuestionnaireSubmission from "./useQuestionnaireSubmission";
import useQuestionnaireInitialization from "./useQuestionnaireInitialization";

/**
 * Manages the state and logic for the alumni questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @param {Boolean} options.isPreview - Whether the questionnaire is in preview mode
 * @returns {Object} The questionnaire state and methods
 */
export default function useAlumniQuestionnaire(options) {
    const { questionnaire, isPreview = false } = options;

    // Initialize the state management
    const state = useQuestionnaireState({
        questionnaire,
    });

    // Initialize screens information
    const screens = useQuestionnaireScreens({
        questionnaire,
    });

    // Initialize validation
    const validation = useQuestionnaireValidation(state);

    // Initialize navigation with validation function
    const navigation = useQuestionnaireNavigation(
        state,
        validation.validateCurrentSection
    );

    // Initialize submission
    const submission = useQuestionnaireSubmission({
        questionnaire,
        state,
        isPreview,
        validateSection: validation.validateCurrentSection,
        showThankYou: navigation.showThankYouScreen,
    });

    // Initialize questionnaire initialization
    const initialization = useQuestionnaireInitialization({
        questionnaire,
        state,
    });

    // Setup initialization on mount
    const initializeQuestionnaire = () => {
        initialization.initializeQuestionnaire();
    };

    return {
        // State
        isLoading: state.isLoading,
        isSubmitting: state.isSubmitting,
        currentStep: state.currentStep,
        currentSectionIndex: state.currentSectionIndex,
        answers: state.answers,
        errors: state.errors,
        validationState: state.validationState,

        // Computed
        totalSections: state.totalSections,
        currentSection: state.currentSection,
        progress: state.progress,

        // Screen content
        ...screens,

        // Methods
        startQuestionnaire: navigation.startQuestionnaire,
        previousSection: navigation.previousSection,
        nextSection: navigation.nextSection,
        handleValidation: validation.handleValidation,
        submitQuestionnaire: submission.submitQuestionnaire,
        initializeQuestionnaire,
    };
}
