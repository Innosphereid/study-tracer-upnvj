/**
 * Actions for managing sections in the questionnaire
 * Contains operations for adding, updating, and managing sections
 *
 * @module questionnaire/store/actions/sections
 */

import { v4 as uuidv4 } from "uuid";

/**
 * Collection of actions for managing sections
 * @type {Object}
 */
export default {
    /**
     * Add a new section to the questionnaire
     */
    addSection() {
        const newSection = {
            id: uuidv4(),
            title: `Seksi ${this.totalSections + 1}`,
            description: "",
            questions: [],
        };

        this.questionnaire.sections.push(newSection);
        this.currentSectionIndex = this.questionnaire.sections.length - 1;
        this.currentQuestionIndex = -1;
        this.selectedComponent = { type: "section", id: newSection.id };

        this.saveQuestionnaire();
    },

    /**
     * Duplicate an existing section
     * @param {string} sectionId - The ID of the section to duplicate
     */
    duplicateSection(sectionId) {
        const sectionIndex = this.questionnaire.sections.findIndex(
            (s) => s.id === sectionId
        );

        if (sectionIndex < 0) return;

        const originalSection = this.questionnaire.sections[sectionIndex];

        const duplicatedSection = {
            ...JSON.parse(JSON.stringify(originalSection)),
            id: uuidv4(),
            title: `${originalSection.title} (copy)`,
            questions: originalSection.questions.map((q) => ({
                ...q,
                id: uuidv4(),
            })),
        };

        this.questionnaire.sections.splice(
            sectionIndex + 1,
            0,
            duplicatedSection
        );
        this.currentSectionIndex = sectionIndex + 1;
        this.currentQuestionIndex = -1;
        this.selectedComponent = {
            type: "section",
            id: duplicatedSection.id,
        };

        this.saveQuestionnaire();
    },

    /**
     * Delete a section
     * @param {string} sectionId - The ID of the section to delete
     */
    deleteSection(sectionId) {
        const sectionIndex = this.questionnaire.sections.findIndex(
            (s) => s.id === sectionId
        );

        if (sectionIndex < 0) return;

        this.questionnaire.sections.splice(sectionIndex, 1);

        if (
            this.selectedComponent?.type === "section" &&
            this.selectedComponent.id === sectionId
        ) {
            this.selectedComponent = null;
        }

        if (this.currentSectionIndex >= this.questionnaire.sections.length) {
            this.currentSectionIndex = Math.max(
                0,
                this.questionnaire.sections.length - 1
            );
        }

        this.currentQuestionIndex = -1;

        this.saveQuestionnaire();
    },

    /**
     * Update a section with new data
     * @param {string} sectionId - The ID of the section to update
     * @param {Object} updates - The updates to apply to the section
     */
    updateSection(sectionId, updates) {
        const sectionIndex = this.questionnaire.sections.findIndex(
            (s) => s.id === sectionId
        );

        if (sectionIndex < 0) return;

        this.questionnaire.sections[sectionIndex] = {
            ...this.questionnaire.sections[sectionIndex],
            ...updates,
        };

        this.saveQuestionnaire();
    },

    /**
     * Reorder sections
     * @param {Array} newOrder - The new order as array of indices
     */
    reorderSections(newOrder) {
        // Asumsikan newOrder adalah array indeks baru untuk seksi
        const reorderedSections = newOrder.map(
            (index) => this.questionnaire.sections[index]
        );
        this.questionnaire.sections = reorderedSections;

        this.saveQuestionnaire();
    },
};
