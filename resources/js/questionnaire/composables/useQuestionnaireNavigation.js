/**
 * @fileoverview Composable for managing questionnaire navigation
 *
 * This composable handles the navigation between different sections and steps
 * of the questionnaire, including validation before navigation.
 */

/**
 * Manages navigation for a questionnaire
 *
 * @param {Object} state - The questionnaire state
 * @param {Object} state.currentStep - The current step ref
 * @param {Object} state.currentSectionIndex - The current section index ref
 * @param {Object} state.totalSections - The total sections computed property
 * @param {Function} validateSection - Function to validate the current section
 * @returns {Object} Navigation methods
 */
export default function useQuestionnaireNavigation(state, validateSection) {
    const { currentStep, currentSectionIndex, totalSections } = state;

    /**
     * Starts the questionnaire and shows the first section
     */
    const startQuestionnaire = () => {
        currentStep.value = "questions";
        currentSectionIndex.value = 0;

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    /**
     * Moves to the previous section
     */
    const previousSection = () => {
        if (currentSectionIndex.value > 0) {
            currentSectionIndex.value--;

            // Scroll to top of new section
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    /**
     * Moves to the next section if the current section is valid
     */
    const nextSection = () => {
        // Validate current section before proceeding
        if (!validateSection()) {
            // Scroll to first error
            const firstErrorElement = document.querySelector(".text-red-600");
            if (firstErrorElement) {
                firstErrorElement.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
            return;
        }

        if (currentSectionIndex.value < totalSections.value - 1) {
            currentSectionIndex.value++;

            // Scroll to top of new section
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    /**
     * Moves to the thank you screen
     */
    const showThankYouScreen = () => {
        currentStep.value = "thankYou";

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    return {
        startQuestionnaire,
        previousSection,
        nextSection,
        showThankYouScreen,
    };
}
