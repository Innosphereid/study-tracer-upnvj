/**
 * @fileoverview Composable for managing questionnaire state
 *
 * This composable is responsible for managing the core state of the questionnaire,
 * including the current step, section, answers, and validation state.
 */

import { ref, computed } from "vue";

/**
 * Manages the state for a questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @returns {Object} The questionnaire state and computed properties
 */
export default function useQuestionnaireState(options) {
    const { questionnaire } = options;

    // Debug flag - hanya diaktifkan jika eksplisit diminta
    const debug = ref(window.location.href.includes("debug=true"));

    // Core state
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const currentStep = ref("welcome");
    const currentSectionIndex = ref(0);
    const answers = ref({});
    const errors = ref({});
    const validationState = ref({});

    // Computed properties
    const totalSections = computed(() => questionnaire.sections.length);

    const currentSection = computed(() => {
        if (
            currentSectionIndex.value >= 0 &&
            currentSectionIndex.value < totalSections.value
        ) {
            return questionnaire.sections[currentSectionIndex.value];
        }
        return null;
    });

    const progress = computed(() => {
        if (totalSections.value === 0) return 0;
        return ((currentSectionIndex.value + 1) / totalSections.value) * 100;
    });

    return {
        // Debug
        debug,

        // State refs
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
    };
}
