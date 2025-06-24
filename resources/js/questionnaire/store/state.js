/**
 * Initial state for questionnaire store
 * Contains all the data structures used by the questionnaire editor
 *
 * @module questionnaire/store/state
 */

/**
 * Initial state for the questionnaire store
 * @returns {Object} The initial state object
 */
export default function state() {
    return {
        questionnaire: {
            id: null,
            title: "Kuesioner Baru",
            description: "",
            slug: "",
            startDate: null,
            endDate: null,
            showProgressBar: true,
            showPageNumbers: true,
            requiresLogin: false,
            sections: [],
            welcomeScreen: {
                title: "Selamat Datang",
                description:
                    "Terima kasih telah berpartisipasi dalam tracer study kami.",
            },
            thankYouScreen: {
                title: "Terima Kasih",
                description:
                    "Terima kasih atas partisipasi Anda dalam tracer study kami.",
            },
        },
        currentSectionIndex: 0,
        currentQuestionIndex: -1,
        selectedComponent: null,
        isDragging: false,
        isEditing: false,
        saveStatus: "idle", // 'idle', 'saving', 'saved', 'error'
        errorMessage: null, // For storing specific error messages
        validationErrors: null, // For storing validation errors
        lastSaved: null,
        autosaveInterval: null,
        originalQuestionnaire: null,
    };
}
