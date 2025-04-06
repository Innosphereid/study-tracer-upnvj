/**
 * Utility functions for questionnaire store
 * Contains helper functions specific to store operations
 *
 * @module questionnaire/store/helpers/utils
 */

import { v4 as uuidv4 } from "uuid";

/**
 * Map frontend question types to backend types
 * @param {string} frontendType - The frontend question type
 * @returns {string} The corresponding backend question type
 */
export function mapQuestionType(frontendType) {
    const typeMap = {
        "short-text": "text",
        "long-text": "textarea",
        radio: "radio",
        checkbox: "checkbox",
        dropdown: "dropdown",
        rating: "rating",
        date: "date",
        "file-upload": "file",
        matrix: "matrix",
        email: "text",
        phone: "text",
        number: "text",
        "yes-no": "yes-no",
        slider: "rating",
        ranking: "matrix",
        likert: "likert",
    };

    return typeMap[frontendType] || "text"; // Default to text if mapping not found
}

/**
 * Map backend question types to frontend types
 * @param {string} backendType - The backend question type
 * @returns {string} The corresponding frontend question type
 */
export function mapFrontendQuestionType(backendType) {
    const typeMap = {
        text: "short-text",
        textarea: "long-text",
        radio: "radio",
        checkbox: "checkbox",
        dropdown: "dropdown",
        rating: "rating",
        date: "date",
        file: "file-upload",
        matrix: "matrix",
        likert: "likert",
        "yes-no": "yes-no",
        slider: "slider",
        email: "email",
        phone: "phone",
        number: "number",
    };

    return typeMap[backendType] || "short-text";
}

/**
 * Handle special cases for file upload questions
 * @param {Object} question - The question object
 */
export function specialHandlingForFileUpload(question) {
    if (
        question.type === "file-upload" &&
        question.allowedTypes &&
        Array.isArray(question.allowedTypes) &&
        question.allowedTypes.includes("*/*")
    ) {
        console.log("Store: Special handling for */* allowedTypes");

        // Ensure we're not mixing allowedTypes
        question.allowedTypes = ["*/*"];

        // Make sure settings also has the correct allowedTypes
        if (question.settings && typeof question.settings === "object") {
            question.settings.allowedTypes = ["*/*"];
        }
    }
}

/**
 * Apply special handling for different question types
 * Ensures that all question types have the necessary data structures
 * required by their respective components.
 *
 * @param {Object} question - The question object to process
 * @returns {Object} - The processed question object
 */
export function specialHandlingForQuestionType(question) {
    // Handle file upload questions
    if (question.type === "file-upload") {
        specialHandlingForFileUpload(question);
    }

    // Handle matrix questions
    if (question.type === "matrix") {
        if (!question.rows) {
            question.rows = [
                { id: uuidv4(), text: "Row 1" },
                { id: uuidv4(), text: "Row 2" },
            ];
        }

        if (!question.columns) {
            question.columns = [
                { id: uuidv4(), text: "Column 1" },
                { id: uuidv4(), text: "Column 2" },
            ];
        }
    }

    // Handle rating questions
    if (question.type === "rating") {
        if (!question.maxRating) {
            question.maxRating = 5;
        }
    }

    // Handle likert questions
    if (question.type === "likert") {
        if (!question.likertOptions) {
            question.likertOptions = [
                { id: uuidv4(), text: "Sangat Tidak Setuju" },
                { id: uuidv4(), text: "Tidak Setuju" },
                { id: uuidv4(), text: "Netral" },
                { id: uuidv4(), text: "Setuju" },
                { id: uuidv4(), text: "Sangat Setuju" },
            ];
        }

        if (!question.statements) {
            question.statements = [
                { id: uuidv4(), text: "Pernyataan 1" },
                { id: uuidv4(), text: "Pernyataan 2" },
            ];
        }
    }

    return question;
}

/**
 * Check if an ID is temporary (either starts with temp_ or is a UUID)
 * @param {string|number} id - The ID to check
 * @returns {boolean} True if the ID is temporary
 */
export function isTemporaryId(id) {
    if (!id) return true;
    if (typeof id === "string" && id.startsWith("temp_")) return true;
    // Check if it's a UUID format (simple check for presence of hyphens and length)
    if (typeof id === "string" && id.includes("-") && id.length > 30)
        return true;
    return false;
}
