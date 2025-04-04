/**
 * @fileoverview Composable for managing questionnaire validation
 *
 * This composable handles the validation of question answers, tracking
 * validation state, and providing error messages.
 */

/**
 * Manages validation for a questionnaire
 *
 * @param {Object} state - The questionnaire state
 * @param {Object} state.errors - The errors ref
 * @param {Object} state.validationState - The validation state ref
 * @param {Object} state.currentSection - The current section computed property
 * @returns {Object} Validation methods
 */
export default function useQuestionnaireValidation(state) {
    const { errors, validationState, currentSection } = state;

    /**
     * Handles validation results for a question
     *
     * @param {Object} validationResult - The validation result object
     * @param {String} questionId - The ID of the question being validated
     */
    const handleValidation = (validationResult, questionId) => {
        validationState.value[questionId] = validationResult;

        if (!validationResult.isValid) {
            errors.value[questionId] = validationResult.errorMessage;
        } else {
            delete errors.value[questionId];
        }
    };

    /**
     * Validates all questions in the current section
     *
     * @returns {Boolean} Whether all questions in the current section are valid
     */
    const validateCurrentSection = () => {
        let isValid = true;

        // Check if current section exists
        if (!currentSection.value) return true;

        // Validate required questions in the current section
        currentSection.value.questions.forEach((question) => {
            if (question.required) {
                // Get validation result if available
                const validation = validationState.value[question.id];

                if (!validation || !validation.isValid) {
                    errors.value[question.id] = "Pertanyaan ini wajib dijawab.";
                    isValid = false;
                }
            }
        });

        return isValid;
    };

    return {
        handleValidation,
        validateCurrentSection,
    };
}
