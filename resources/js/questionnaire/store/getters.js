/**
 * Getters for questionnaire store
 * Provides computed properties for accessing and deriving data from the store state
 *
 * @module questionnaire/store/getters
 */

/**
 * Collection of getters for the questionnaire store
 * @type {Object}
 */
export default {
    /**
     * Get the current active section
     * @param {Object} state - Store state
     * @returns {Object|null} The current section or null if none exists
     */
    currentSection: (state) => {
        return state.questionnaire.sections[state.currentSectionIndex] || null;
    },

    /**
     * Get the current active question
     * @param {Object} state - Store state
     * @returns {Object|null} The current question or null if none exists
     */
    currentQuestion: (state) => {
        if (!state.currentSection || state.currentQuestionIndex < 0)
            return null;
        return (
            state.currentSection.questions[state.currentQuestionIndex] || null
        );
    },

    /**
     * Get the total number of sections
     * @param {Object} state - Store state
     * @returns {number} Total number of sections
     */
    totalSections: (state) => state.questionnaire.sections.length,

    /**
     * Check if a question can be added (requires at least one section)
     * @param {Object} state - Store state
     * @returns {boolean} True if questions can be added
     */
    canAddQuestion: (state) => state.questionnaire.sections.length > 0,

    /**
     * Get the available question types
     * @returns {Array} Array of question type objects
     */
    questionTypes: () => [
        {
            id: "short-text",
            name: "Teks Pendek",
            icon: "text",
            category: "dasar",
        },
        {
            id: "long-text",
            name: "Teks Panjang",
            icon: "paragraph",
            category: "dasar",
        },
        {
            id: "email",
            name: "Email",
            icon: "mail",
            category: "dasar",
        },
        {
            id: "phone",
            name: "Nomor Telepon",
            icon: "phone",
            category: "dasar",
        },
        {
            id: "number",
            name: "Angka",
            icon: "number",
            category: "dasar",
        },
        {
            id: "date",
            name: "Tanggal",
            icon: "calendar",
            category: "dasar",
        },
        {
            id: "radio",
            name: "Pilihan Ganda",
            icon: "radio",
            category: "pilihan",
        },
        {
            id: "checkbox",
            name: "Kotak Centang",
            icon: "checkbox",
            category: "pilihan",
        },
        {
            id: "dropdown",
            name: "Dropdown",
            icon: "dropdown",
            category: "pilihan",
        },
        {
            id: "rating",
            name: "Rating Bintang",
            icon: "star",
            category: "pilihan",
        },
        {
            id: "likert",
            name: "Skala Likert",
            icon: "scale",
            category: "pilihan",
        },
        {
            id: "yes-no",
            name: "Ya/Tidak",
            icon: "boolean",
            category: "pilihan",
        },
        {
            id: "file-upload",
            name: "Upload File",
            icon: "upload",
            category: "lanjutan",
        },
        {
            id: "matrix",
            name: "Matriks Pilihan",
            icon: "matrix",
            category: "lanjutan",
        },
        {
            id: "slider",
            name: "Slider",
            icon: "slider",
            category: "lanjutan",
        },
        // Temporarily disabled due to a bug with drag and drop functionality
        // that causes navigation to first page when interacting with ranking items
        // TODO: Fix the bug and re-enable this component
        /* {
            id: "ranking",
            name: "Rangking",
            icon: "ranking",
            category: "lanjutan",
        }, */
    ],

    /**
     * Format the last saved timestamp
     * @param {Object} state - Store state
     * @returns {string} Formatted date string
     */
    formattedLastSaved: (state) => {
        if (!state.lastSaved) return "";
        return new Date(state.lastSaved).toLocaleString("id-ID");
    },
};
