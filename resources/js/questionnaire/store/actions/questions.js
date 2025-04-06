/**
 * Actions for managing questions in the questionnaire
 * Contains operations for adding, updating, and managing questions
 *
 * @module questionnaire/store/actions/questions
 */

import { v4 as uuidv4 } from "uuid";
import {
    specialHandlingForFileUpload,
    specialHandlingForQuestionType,
} from "../helpers/utils";

/**
 * Collection of actions for managing questions
 * @type {Object}
 */
export default {
    /**
     * Add a new question to the current section
     * @param {string} questionType - The type of question to add
     */
    addQuestion(questionType) {
        if (!this.canAddQuestion) {
            this.addSection();
        }

        const newQuestion = this.createQuestion(questionType);

        this.questionnaire.sections[this.currentSectionIndex].questions.push(
            newQuestion
        );
        this.currentQuestionIndex =
            this.questionnaire.sections[this.currentSectionIndex].questions
                .length - 1;
        this.selectedComponent = { type: "question", id: newQuestion.id };

        this.saveQuestionnaire();
    },

    /**
     * Add a new question at a specific position
     * @param {string} questionType - The type of question to add
     * @param {number} position - The position to insert the question
     */
    addQuestionAtPosition(questionType, position) {
        if (!this.canAddQuestion) {
            this.addSection();
            position = 0; // If we had to create a new section, force position to 0
        }

        const newQuestion = this.createQuestion(questionType);
        const sectionQuestions =
            this.questionnaire.sections[this.currentSectionIndex].questions;

        // Validate position - ensure it's within bounds
        if (position < 0) position = 0;
        if (position > sectionQuestions.length)
            position = sectionQuestions.length;

        // Insert question at the specified position
        sectionQuestions.splice(position, 0, newQuestion);

        // Update current question index and selected component
        this.currentQuestionIndex = position;
        this.selectedComponent = { type: "question", id: newQuestion.id };

        this.saveQuestionnaire();
    },

    /**
     * Create a new question object based on type
     * @param {string} type - The type of question to create
     * @returns {Object} The newly created question object
     */
    createQuestion(type) {
        const baseQuestion = {
            id: uuidv4(),
            type: type,
            text: "Pertanyaan baru",
            helpText: "",
            required: false,
            visible: true,
        };

        // Tambahkan properti spesifik berdasarkan tipe pertanyaan
        switch (type) {
            case "short-text":
            case "long-text":
                return {
                    ...baseQuestion,
                    placeholder: "",
                    maxLength: 0, // 0 berarti tidak ada batasan
                };

            case "email":
                return {
                    ...baseQuestion,
                    placeholder: "email@example.com",
                };

            case "phone":
                return {
                    ...baseQuestion,
                    placeholder: "+62",
                    format: "international",
                };

            case "number":
                return {
                    ...baseQuestion,
                    placeholder: "0",
                    min: null,
                    max: null,
                    step: 1,
                };

            case "date":
                return {
                    ...baseQuestion,
                    format: "DD/MM/YYYY",
                    minDate: null,
                    maxDate: null,
                };

            case "radio":
            case "checkbox":
            case "dropdown":
                return {
                    ...baseQuestion,
                    options: [
                        { id: uuidv4(), text: "Opsi 1", value: "option_1" },
                        { id: uuidv4(), text: "Opsi 2", value: "option_2" },
                        { id: uuidv4(), text: "Opsi 3", value: "option_3" },
                    ],
                    allowOther: false,
                };

            case "rating":
                return {
                    ...baseQuestion,
                    maxRating: 5,
                    labels: {
                        1: "Sangat Buruk",
                        5: "Sangat Baik",
                    },
                };

            case "likert":
                return {
                    ...baseQuestion,
                    statements: [{ id: uuidv4(), text: "Pernyataan 1" }],
                    scale: [
                        { value: 1, label: "Sangat Tidak Setuju" },
                        { value: 2, label: "Tidak Setuju" },
                        { value: 3, label: "Netral" },
                        { value: 4, label: "Setuju" },
                        { value: 5, label: "Sangat Setuju" },
                    ],
                };

            case "yes-no":
                return {
                    ...baseQuestion,
                    yesLabel: "Ya",
                    noLabel: "Tidak",
                };

            case "file-upload":
                return {
                    ...baseQuestion,
                    allowedTypes: ["image/*", "application/pdf"],
                    maxSize: 5, // MB
                    maxFiles: 1,
                };

            case "matrix":
                return {
                    ...baseQuestion,
                    rows: [
                        { id: uuidv4(), text: "Baris 1" },
                        { id: uuidv4(), text: "Baris 2" },
                    ],
                    columns: [
                        { id: uuidv4(), text: "Kolom 1" },
                        { id: uuidv4(), text: "Kolom 2" },
                        { id: uuidv4(), text: "Kolom 3" },
                    ],
                    matrixType: "radio", // atau 'checkbox'
                };

            case "slider":
                return {
                    ...baseQuestion,
                    min: 0,
                    max: 100,
                    step: 1,
                    showTicks: true,
                    showLabels: true,
                    labels: {
                        0: "Minimum",
                        100: "Maximum",
                    },
                };

            case "ranking":
                return {
                    ...baseQuestion,
                    options: [
                        { id: uuidv4(), text: "Item 1" },
                        { id: uuidv4(), text: "Item 2" },
                        { id: uuidv4(), text: "Item 3" },
                    ],
                };

            default:
                return baseQuestion;
        }
    },

    /**
     * Duplicate an existing question
     * @param {string} questionId - The ID of the question to duplicate
     */
    duplicateQuestion(questionId) {
        const sectionIndex = this.questionnaire.sections.findIndex((section) =>
            section.questions.some((q) => q.id === questionId)
        );

        if (sectionIndex < 0) return;

        const questionIndex = this.questionnaire.sections[
            sectionIndex
        ].questions.findIndex((q) => q.id === questionId);
        const originalQuestion =
            this.questionnaire.sections[sectionIndex].questions[questionIndex];

        const duplicatedQuestion = {
            ...JSON.parse(JSON.stringify(originalQuestion)),
            id: uuidv4(),
            text: `${originalQuestion.text} (copy)`,
        };

        this.questionnaire.sections[sectionIndex].questions.splice(
            questionIndex + 1,
            0,
            duplicatedQuestion
        );
        this.currentSectionIndex = sectionIndex;
        this.currentQuestionIndex = questionIndex + 1;
        this.selectedComponent = {
            type: "question",
            id: duplicatedQuestion.id,
        };

        this.saveQuestionnaire();
    },

    /**
     * Delete a question
     * @param {string} questionId - The ID of the question to delete
     */
    deleteQuestion(questionId) {
        const sectionIndex = this.questionnaire.sections.findIndex((section) =>
            section.questions.some((q) => q.id === questionId)
        );

        if (sectionIndex < 0) return;

        const questionIndex = this.questionnaire.sections[
            sectionIndex
        ].questions.findIndex((q) => q.id === questionId);

        this.questionnaire.sections[sectionIndex].questions.splice(
            questionIndex,
            1
        );

        if (
            this.selectedComponent?.type === "question" &&
            this.selectedComponent.id === questionId
        ) {
            this.selectedComponent = null;
            this.currentQuestionIndex = -1;
        }

        this.saveQuestionnaire();
    },

    /**
     * Update a question with new data
     * @param {string} questionId - The ID of the question to update
     * @param {Object} updates - The updates to apply to the question
     */
    updateQuestion(questionId, updates) {
        specialHandlingForFileUpload(updates);

        const sectionIndex = this.questionnaire.sections.findIndex((section) =>
            section.questions.some((q) => q.id === questionId)
        );

        if (sectionIndex < 0) return;

        const questionIndex = this.questionnaire.sections[
            sectionIndex
        ].questions.findIndex((q) => q.id === questionId);

        this.questionnaire.sections[sectionIndex].questions[questionIndex] = {
            ...this.questionnaire.sections[sectionIndex].questions[
                questionIndex
            ],
            ...updates,
        };

        this.saveQuestionnaire();
    },

    /**
     * Reorder questions within a section
     * @param {string} sectionId - The ID of the section containing the questions
     * @param {Array} newOrder - The new order as array of indices
     */
    reorderQuestions(sectionId, newOrder) {
        const sectionIndex = this.questionnaire.sections.findIndex(
            (s) => s.id === sectionId
        );

        if (sectionIndex < 0) return;

        const reorderedQuestions = newOrder.map(
            (index) =>
                this.questionnaire.sections[sectionIndex].questions[index]
        );

        this.questionnaire.sections[sectionIndex].questions =
            reorderedQuestions;
        this.saveQuestionnaire();
    },
};
