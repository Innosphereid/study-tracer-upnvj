/**
 * Questionnaire store module
 * Provides state management for the questionnaire editor
 *
 * @module questionnaire/store
 */

import state from "./state";
import getters from "./getters";
import actions from "./actions";
import { mapQuestionType, specialHandlingForFileUpload } from "./helpers/utils";

/**
 * Create and return the questionnaire store
 * This is a factory function to allow for proper store initialization
 * and testing isolation
 *
 * @returns {Object} The questionnaire store object
 */
export default function createQuestionnaireStore() {
    return {
        // Use function to ensure fresh state copy on initialization
        ...state(),
        ...getters,
        ...actions,

        // Make utility methods accessible on the store for components
        mapQuestionType,
        specialHandlingForFileUpload,
    };
}
