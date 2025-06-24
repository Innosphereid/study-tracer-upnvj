/**
 * Questionnaire store using Pinia
 * This is a wrapper around the refactored questionnaire store implementation
 * It maintains compatibility with existing code while using the refactored modules.
 *
 * @module questionnaire/store/questionnaire
 */

import { defineStore } from "pinia";
import createQuestionnaireStore from "./index";

/**
 * Create a Pinia store from our refactored questionnaire store
 * Maintains compatibility with existing code by exposing the same API
 */
export const useQuestionnaireStore = defineStore("questionnaire", {
    state: () => {
        // Get the state from our refactored implementation
        const store = createQuestionnaireStore();

        // Return a clean copy to prevent reference issues
        return JSON.parse(JSON.stringify(store));
    },

    getters: {
        // Import all getters from the refactored store
        currentSection: (state) => {
            return (
                state.questionnaire.sections[state.currentSectionIndex] || null
            );
        },

        currentQuestion: (state) => {
            if (!state.currentSection || state.currentQuestionIndex < 0)
                return null;
            return (
                state.currentSection.questions[state.currentQuestionIndex] ||
                null
            );
        },

        totalSections: (state) => state.questionnaire.sections.length,

        canAddQuestion: (state) => state.questionnaire.sections.length > 0,

        questionTypes: () => {
            // Use our refactored store's questionTypes getter
            const store = createQuestionnaireStore();
            return store.questionTypes();
        },

        formattedLastSaved: (state) => {
            if (!state.lastSaved) return "";
            return new Date(state.lastSaved).toLocaleString("id-ID");
        },
    },

    actions: {
        // Re-export all actions from refactored modules

        // Core actions
        initializeQuestionnaire(data) {
            const store = createQuestionnaireStore();
            store.initializeQuestionnaire.call(this, data);
        },

        saveQuestionnaire() {
            const store = createQuestionnaireStore();
            return store.saveQuestionnaire.call(this);
        },

        loadQuestionnaire(id) {
            const store = createQuestionnaireStore();
            return store.loadQuestionnaire.call(this, id);
        },

        loadDraft() {
            const store = createQuestionnaireStore();
            return store.loadDraft.call(this);
        },

        slugify(text) {
            const store = createQuestionnaireStore();
            return store.slugify(text);
        },

        // Section actions
        addSection() {
            const store = createQuestionnaireStore();
            store.addSection.call(this);
        },

        duplicateSection(sectionId) {
            const store = createQuestionnaireStore();
            store.duplicateSection.call(this, sectionId);
        },

        deleteSection(sectionId) {
            const store = createQuestionnaireStore();
            store.deleteSection.call(this, sectionId);
        },

        updateSection(sectionId, updates) {
            const store = createQuestionnaireStore();
            store.updateSection.call(this, sectionId, updates);
        },

        reorderSections(newOrder) {
            const store = createQuestionnaireStore();
            store.reorderSections.call(this, newOrder);
        },

        // Question actions
        addQuestion(questionType) {
            const store = createQuestionnaireStore();
            store.addQuestion.call(this, questionType);
        },

        addQuestionAtPosition(questionType, position) {
            const store = createQuestionnaireStore();
            store.addQuestionAtPosition.call(this, questionType, position);
        },

        createQuestion(type) {
            const store = createQuestionnaireStore();
            return store.createQuestion.call(this, type);
        },

        duplicateQuestion(questionId) {
            const store = createQuestionnaireStore();
            store.duplicateQuestion.call(this, questionId);
        },

        deleteQuestion(questionId) {
            const store = createQuestionnaireStore();
            store.deleteQuestion.call(this, questionId);
        },

        updateQuestion(questionId, updates) {
            const store = createQuestionnaireStore();
            store.updateQuestion.call(this, questionId, updates);
        },

        reorderQuestions(sectionId, newOrder) {
            const store = createQuestionnaireStore();
            store.reorderQuestions.call(this, sectionId, newOrder);
        },

        // UI actions
        selectComponent(type, id) {
            const store = createQuestionnaireStore();
            store.selectComponent.call(this, type, id);
        },

        updateQuestionnaireSettings(settings) {
            const store = createQuestionnaireStore();
            store.updateQuestionnaireSettings.call(this, settings);
        },

        updateWelcomeScreen(updates) {
            const store = createQuestionnaireStore();
            store.updateWelcomeScreen.call(this, updates);
        },

        updateThankYouScreen(updates) {
            const store = createQuestionnaireStore();
            store.updateThankYouScreen.call(this, updates);
        },

        setupAutosave() {
            const store = createQuestionnaireStore();
            store.setupAutosave.call(this);
        },

        resetState() {
            const store = createQuestionnaireStore();
            store.resetState.call(this);
        },

        // Publishing actions
        exportQuestionnaire() {
            const store = createQuestionnaireStore();
            store.exportQuestionnaire.call(this);
        },

        publishQuestionnaire() {
            const store = createQuestionnaireStore();
            return store.publishQuestionnaire.call(this);
        },

        // Utility methods that need to be available for components
        mapQuestionType(frontendType) {
            const store = createQuestionnaireStore();
            return store.mapQuestionType(frontendType);
        },

        specialHandlingForFileUpload(question) {
            const store = createQuestionnaireStore();
            store.specialHandlingForFileUpload(question);
        },
    },
});
