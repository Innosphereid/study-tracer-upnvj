/**
 * Actions for managing the UI state of the questionnaire editor
 * Contains operations for managing selection, visibility, and UI interactions
 *
 * @module questionnaire/store/actions/ui
 */

/**
 * Collection of actions for managing UI state
 * @type {Object}
 */
export default {
    /**
     * Select a component (section or question)
     * @param {string} type - The type of component ('section' or 'question')
     * @param {string} id - The ID of the component
     */
    selectComponent(type, id) {
        this.selectedComponent = { type, id };

        if (type === "section") {
            const sectionIndex = this.questionnaire.sections.findIndex(
                (s) => s.id === id
            );
            if (sectionIndex >= 0) {
                this.currentSectionIndex = sectionIndex;
                this.currentQuestionIndex = -1;
            }
        } else if (type === "question") {
            // Temukan seksi dan pertanyaan yang sesuai dengan ID
            for (let i = 0; i < this.questionnaire.sections.length; i++) {
                const questionIndex = this.questionnaire.sections[
                    i
                ].questions.findIndex((q) => q.id === id);
                if (questionIndex >= 0) {
                    this.currentSectionIndex = i;
                    this.currentQuestionIndex = questionIndex;
                    break;
                }
            }
        }
    },

    /**
     * Update questionnaire settings
     * @param {Object} settings - The new settings to apply
     */
    updateQuestionnaireSettings(settings) {
        this.questionnaire = {
            ...this.questionnaire,
            ...settings,
        };

        this.saveQuestionnaire();
    },

    /**
     * Update welcome screen content
     * @param {Object} updates - The updates to apply to the welcome screen
     */
    updateWelcomeScreen(updates) {
        this.questionnaire.welcomeScreen = {
            ...this.questionnaire.welcomeScreen,
            ...updates,
        };

        this.saveQuestionnaire();
    },

    /**
     * Update thank you screen content
     * @param {Object} updates - The updates to apply to the thank you screen
     */
    updateThankYouScreen(updates) {
        this.questionnaire.thankYouScreen = {
            ...this.questionnaire.thankYouScreen,
            ...updates,
        };

        this.saveQuestionnaire();
    },

    /**
     * Set up autosave interval
     */
    setupAutosave() {
        // Hapus interval sebelumnya jika sudah ada
        if (this.autosaveInterval) {
            clearInterval(this.autosaveInterval);
        }

        // Set interval baru untuk auto-save setiap 1 menit
        this.autosaveInterval = setInterval(() => {
            this.saveQuestionnaire();
        }, 60000); // 60 detik
    },

    /**
     * Reset the store state
     */
    resetState() {
        // Clear any autosave interval
        if (this.autosaveInterval) {
            clearInterval(this.autosaveInterval);
            this.autosaveInterval = null;
        }

        // Reset to initial state
        this.questionnaire = {
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
        };
        this.currentSectionIndex = 0;
        this.currentQuestionIndex = -1;
        this.selectedComponent = null;
        this.isDragging = false;
        this.isEditing = false;
        this.saveStatus = "idle";
        this.errorMessage = null;
        this.validationErrors = null;
        this.lastSaved = null;
        this.originalQuestionnaire = null;
    },
};
