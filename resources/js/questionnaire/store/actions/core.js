/**
 * Core actions for questionnaire management
 * Contains the primary operations for loading, initializing, and saving questionnaires
 *
 * @module questionnaire/store/actions/core
 */

import axios from "axios";
import {
    mapFrontendQuestionType,
    specialHandlingForQuestionType,
    isTemporaryId,
} from "../helpers/utils";
import { v4 as uuidv4 } from "uuid";
import { slugify } from "../../utils/helpers";

/**
 * Collection of core actions for questionnaire management
 * @type {Object}
 */
export default {
    /**
     * Initialize the questionnaire with data from the server
     * Transforms backend data format to frontend format
     * @param {Object} data - The questionnaire data from the backend
     */
    initializeQuestionnaire(data) {
        console.log("Initializing questionnaire with data:", data);

        if (data && Object.keys(data).length > 0) {
            // Save original data
            this.originalQuestionnaire = JSON.parse(JSON.stringify(data));

            // Create a clean copy to prevent reference issues
            const newQuestionnaire = { ...this.questionnaire };

            // Copy basic properties
            newQuestionnaire.id = data.id;
            newQuestionnaire.title = data.title || newQuestionnaire.title;
            newQuestionnaire.description =
                data.description || newQuestionnaire.description;
            newQuestionnaire.slug = data.slug || newQuestionnaire.slug;
            newQuestionnaire.status = data.status || newQuestionnaire.status;

            // Copy dates if available
            if (data.start_date) {
                newQuestionnaire.startDate = data.start_date;
            }
            if (data.end_date) {
                newQuestionnaire.endDate = data.end_date;
            }

            // Debug information
            console.log("Processing questionnaire settings");
            console.log("Settings data:", data.settings);
            console.log("Has sections:", Array.isArray(data.sections));
            console.log(
                "Sections count:",
                Array.isArray(data.sections) ? data.sections.length : 0
            );

            // Handle settings
            if (data.settings) {
                const settings =
                    typeof data.settings === "string"
                        ? JSON.parse(data.settings)
                        : data.settings;

                console.log("Parsed settings:", settings);

                // Copy settings
                newQuestionnaire.showProgressBar =
                    settings.showProgressBar ??
                    newQuestionnaire.showProgressBar;
                newQuestionnaire.showPageNumbers =
                    settings.showPageNumbers ??
                    newQuestionnaire.showPageNumbers;
                newQuestionnaire.requiresLogin =
                    settings.requiresLogin ?? newQuestionnaire.requiresLogin;

                // Copy welcome screen
                if (settings.welcomeScreen) {
                    newQuestionnaire.welcomeScreen = {
                        title:
                            settings.welcomeScreen.title ||
                            newQuestionnaire.welcomeScreen.title,
                        description:
                            settings.welcomeScreen.description ||
                            newQuestionnaire.welcomeScreen.description,
                    };
                }

                // Copy thank you screen
                if (settings.thankYouScreen) {
                    newQuestionnaire.thankYouScreen = {
                        title:
                            settings.thankYouScreen.title ||
                            newQuestionnaire.thankYouScreen.title,
                        description:
                            settings.thankYouScreen.description ||
                            newQuestionnaire.thankYouScreen.description,
                    };
                }
            }

            // Process sections
            if (data.sections && Array.isArray(data.sections)) {
                console.log(`Processing ${data.sections.length} sections`);

                newQuestionnaire.sections = data.sections.map(
                    (section, index) => {
                        console.log(`Processing section #${index}:`, section);

                        // Ensure section has an id (use existing or generate new)
                        const sectionId = section.id || uuidv4();

                        // Create base section object
                        const newSection = {
                            id: sectionId,
                            title:
                                section.title ||
                                `Seksi ${data.sections.indexOf(section) + 1}`,
                            description: section.description || "",
                            questions: [],
                        };

                        // Process section settings if available
                        if (section.settings) {
                            const sectionSettings =
                                typeof section.settings === "string"
                                    ? JSON.parse(section.settings)
                                    : section.settings;

                            // Add settings to section
                            newSection.settings = sectionSettings;
                        }

                        // Process questions
                        if (
                            section.questions &&
                            Array.isArray(section.questions)
                        ) {
                            console.log(
                                `Section ${sectionId} has ${section.questions.length} questions`
                            );

                            newSection.questions = section.questions.map(
                                (question, qIndex) => {
                                    console.log(
                                        `Processing question #${qIndex}:`,
                                        question
                                    );

                                    // Ensure question has correct type format
                                    // Map backend question_type to frontend type if needed
                                    let questionType =
                                        question.type ||
                                        (question.question_type
                                            ? mapFrontendQuestionType(
                                                  question.question_type
                                              )
                                            : "short-text");

                                    console.log(
                                        `Question type mapped from ${
                                            question.question_type ||
                                            question.type
                                        } to ${questionType}`
                                    );

                                    // Create base question
                                    const newQuestion = {
                                        id: question.id || uuidv4(),
                                        type: questionType,
                                        text:
                                            question.text ||
                                            question.title ||
                                            "Untitled Question",
                                        helpText:
                                            question.helpText ||
                                            question.description ||
                                            "",
                                        required:
                                            question.required ||
                                            question.is_required ||
                                            false,
                                    };

                                    // Process question settings
                                    if (question.settings) {
                                        try {
                                            const questionSettings =
                                                typeof question.settings ===
                                                "string"
                                                    ? JSON.parse(
                                                          question.settings
                                                      )
                                                    : question.settings;

                                            // Add settings to question
                                            Object.assign(
                                                newQuestion,
                                                questionSettings
                                            );

                                            console.log(
                                                `Applied settings to question #${qIndex}`
                                            );
                                        } catch (error) {
                                            console.error(
                                                `Error parsing question settings for question #${qIndex}:`,
                                                error
                                            );
                                        }
                                    }

                                    // Process options for choice-based questions
                                    if (
                                        question.options &&
                                        Array.isArray(question.options)
                                    ) {
                                        console.log(
                                            `Question #${qIndex} has ${question.options.length} options`
                                        );

                                        newQuestion.options =
                                            question.options.map((option) => ({
                                                id: option.id || uuidv4(),
                                                text:
                                                    option.text ||
                                                    option.title ||
                                                    option.label ||
                                                    "Option",
                                                value:
                                                    option.value ||
                                                    option.text ||
                                                    option.title ||
                                                    option.label ||
                                                    "Option",
                                            }));
                                    } else if (
                                        [
                                            "radio",
                                            "checkbox",
                                            "dropdown",
                                        ].includes(questionType)
                                    ) {
                                        // Create default options for choice-based questions if none provided
                                        console.log(
                                            `Creating default options for ${questionType} question`
                                        );
                                        newQuestion.options = [
                                            {
                                                id: uuidv4(),
                                                text: "Opsi 1",
                                                value: "option_1",
                                            },
                                            {
                                                id: uuidv4(),
                                                text: "Opsi 2",
                                                value: "option_2",
                                            },
                                            {
                                                id: uuidv4(),
                                                text: "Opsi 3",
                                                value: "option_3",
                                            },
                                        ];
                                    }

                                    // Special handling for specific question types
                                    specialHandlingForQuestionType(newQuestion);

                                    return newQuestion;
                                }
                            );
                        } else {
                            console.warn(
                                `Section ${sectionId} has no questions or questions is not an array`
                            );
                        }

                        return newSection;
                    }
                );
            } else {
                console.warn(
                    "Questionnaire has no sections or sections is not an array"
                );
                // Create a default section if none exists
                newQuestionnaire.sections = [
                    {
                        id: uuidv4(),
                        title: "Seksi 1",
                        description: "",
                        questions: [],
                    },
                ];
            }

            // Update state
            this.questionnaire = newQuestionnaire;
            console.log("Questionnaire initialized:", this.questionnaire);
        }
    },

    /**
     * Save questionnaire to the server
     * @returns {Promise} Promise that resolves to the save response
     */
    saveQuestionnaire() {
        this.saveStatus = "saving";
        this.errorMessage = null;
        this.validationErrors = null;
        console.log("Saving questionnaire state:", this.questionnaire);

        // Save in localStorage for backup
        try {
            localStorage.setItem(
                "questionnaire_draft",
                JSON.stringify(this.questionnaire)
            );
        } catch (error) {
            console.error("Failed to save in localStorage:", error);
        }

        // Determine if this is a create or update operation
        const isCreate =
            !this.questionnaire.id || isTemporaryId(this.questionnaire.id);

        // Track original values for detecting changes
        const originalSlug = this.originalQuestionnaire?.slug || null;
        const currentSlug = this.questionnaire.slug || null;

        // Prepare data for API - only include necessary fields
        const questionnaireData = {
            title: this.questionnaire.title,
            description: this.questionnaire.description || null,
            settings: JSON.stringify({
                showProgressBar: this.questionnaire.showProgressBar,
                showPageNumbers: this.questionnaire.showPageNumbers,
                requiresLogin: this.questionnaire.requiresLogin,
                welcomeScreen: this.questionnaire.welcomeScreen,
                thankYouScreen: this.questionnaire.thankYouScreen,
            }),
            // Include sections separately
            sections: this.questionnaire.sections.map((section) => ({
                id: !isTemporaryId(section.id) ? section.id : undefined,
                title: section.title,
                description: section.description,
                order: section.order,
                questions: section.questions.map((question) => ({
                    id: !isTemporaryId(question.id) ? question.id : undefined,
                    question_type: this.mapQuestionType(question.type),
                    title: question.text,
                    description: question.helpText,
                    is_required: question.required,
                    order: question.order,
                    settings: JSON.stringify(question.settings || {}),
                    options: question.options
                        ? question.options.map((option) => ({
                              id: !isTemporaryId(option.id)
                                  ? option.id
                                  : undefined,
                              value: option.value || option.text,
                              label: option.text,
                              order: option.order,
                          }))
                        : [],
                })),
            })),
        };

        // Only include slug for new questionnaires or if explicitly changed
        if (isCreate || (currentSlug !== originalSlug && currentSlug)) {
            questionnaireData.slug = currentSlug;
            console.log(
                `Including slug in request: ${currentSlug} (create: ${isCreate}, changed: ${
                    currentSlug !== originalSlug
                })`
            );
        } else {
            console.log(
                `Not including slug in update request (original: ${originalSlug}, current: ${currentSlug})`
            );
        }

        // Log the data being sent
        console.log("Saving questionnaire data:", questionnaireData);

        // Make sure we have a valid ID for updates - IMPORTANT FIX
        let questionnaireId = this.questionnaire.id;
        if (
            !isCreate &&
            (questionnaireId === null || questionnaireId === undefined)
        ) {
            console.error(
                "Error: Attempting to update questionnaire without valid ID"
            );
            this.errorMessage =
                "Tidak dapat menyimpan kuesioner: ID tidak valid";
            this.saveStatus = "error";
            return Promise.reject({
                success: false,
                message: this.errorMessage,
            });
        }

        // Ensure numeric ID for API requests
        if (!isCreate && questionnaireId) {
            // Convert string IDs to numbers if they're numeric
            if (
                typeof questionnaireId === "string" &&
                /^\d+$/.test(questionnaireId)
            ) {
                questionnaireId = parseInt(questionnaireId, 10);
                console.log("Converted string ID to number:", questionnaireId);
            }
        }

        const url = isCreate ? "/kuesioner" : `/kuesioner/${questionnaireId}`;
        const method = isCreate ? "post" : "put";

        console.log("Saving questionnaire:", {
            isCreate,
            id: questionnaireId,
            idType: typeof questionnaireId,
            url,
            method,
        });

        // Process all questions to ensure proper handling of special cases
        this.questionnaire.sections.forEach((section) => {
            section.questions.forEach((question) => {
                this.specialHandlingForFileUpload(question);
            });
        });

        // Send request to server
        return axios({
            method: method,
            url: url,
            data: questionnaireData,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
        })
            .then((response) => {
                console.log("Save response:", response.data);

                if (response.data.success) {
                    // Update questionnaire ID if this is a new creation
                    if (isCreate && response.data.id) {
                        console.log(
                            `Updating ID from ${this.questionnaire.id} to ${
                                response.data.id
                            } (${typeof response.data.id})`
                        );
                        this.questionnaire.id = response.data.id;

                        // Store original questionnaire data after successful create
                        this.originalQuestionnaire = JSON.parse(
                            JSON.stringify(this.questionnaire)
                        );
                    }

                    if (response.data.slug) {
                        this.questionnaire.slug = response.data.slug;
                        // Update original slug value when server returns a new one
                        if (this.originalQuestionnaire) {
                            this.originalQuestionnaire.slug =
                                response.data.slug;
                        }
                    }

                    this.saveStatus = "saved";
                    this.lastSaved = new Date().toISOString();
                    return response.data;
                } else {
                    console.error(
                        "Failed to save questionnaire:",
                        response.data.message || "No error message provided"
                    );
                    this.errorMessage =
                        response.data.message || "Failed to save questionnaire";
                    this.validationErrors = response.data.errors || null;
                    this.saveStatus = "error";
                    throw new Error(this.errorMessage);
                }
            })
            .catch((error) => {
                console.error("Error saving questionnaire:", error);

                if (error.response) {
                    console.error("Response status:", error.response.status);
                    console.error("Response headers:", error.response.headers);

                    if (error.response.data) {
                        console.error("Response data:", error.response.data);

                        // Check if there are validation errors
                        if (error.response.data.errors) {
                            console.error(
                                "Validation errors:",
                                JSON.stringify(
                                    error.response.data.errors,
                                    null,
                                    2
                                )
                            );
                            this.validationErrors = error.response.data.errors;
                            this.errorMessage =
                                "Validasi gagal. Periksa data kuesioner.";
                        } else if (error.response.data.message) {
                            this.errorMessage = error.response.data.message;
                            this.validationErrors = null;
                        }

                        // Check if there are exception details
                        if (error.response.data.exception) {
                            console.error(
                                "Exception:",
                                error.response.data.exception
                            );
                            console.error("File:", error.response.data.file);
                            console.error("Line:", error.response.data.line);

                            // If we have exception details but no user-friendly message yet
                            if (!this.errorMessage) {
                                this.errorMessage =
                                    "Terjadi kesalahan server. Silakan coba lagi nanti.";
                            }
                        }
                    }
                } else if (error.request) {
                    // Request was made but no response received
                    console.error("No response received:", error.request);
                    this.errorMessage =
                        "Tidak ada respons dari server. Periksa koneksi internet Anda.";
                    this.validationErrors = null;
                } else {
                    // Error in setting up the request
                    console.error("Error setting up request:", error.message);
                    this.errorMessage =
                        "Gagal mengirim permintaan: " + error.message;
                    this.validationErrors = null;
                }

                // If no specific error message has been set, use a default
                if (!this.errorMessage) {
                    this.errorMessage =
                        "Gagal menyimpan kuesioner. Silakan coba lagi.";
                }

                this.saveStatus = "error";
                throw error;
            });
    },

    /**
     * Load a questionnaire by ID
     * @param {number|string} id - The ID of the questionnaire to load
     * @returns {Promise} Promise that resolves to the loaded questionnaire
     */
    loadQuestionnaire(id) {
        if (this.autosaveInterval) {
            clearInterval(this.autosaveInterval);
            this.autosaveInterval = null;
        }

        // Reset state
        this.resetState();

        return axios
            .get(`/kuesioner/${id}`)
            .then((response) => {
                const data = response.data;

                if (data.questionnaire) {
                    this.initializeQuestionnaire(data.questionnaire);

                    // Store the original questionnaire for tracking changes
                    this.originalQuestionnaire = JSON.parse(
                        JSON.stringify(this.questionnaire)
                    );

                    // Setup autosave for existing questionnaires
                    this.setupAutosave();
                    return this.questionnaire;
                } else {
                    throw new Error("Questionnaire data not found");
                }
            })
            .catch((error) => {
                console.error("Failed to load questionnaire:", error);
                throw error;
            });
    },

    /**
     * Load a draft questionnaire from localStorage
     * @returns {boolean} True if draft was loaded successfully
     */
    loadDraft() {
        try {
            const savedDraft = localStorage.getItem("questionnaire_draft");

            if (savedDraft) {
                const parsedDraft = JSON.parse(savedDraft);
                this.initializeQuestionnaire(parsedDraft);

                // Set lastSaved agar user tahu kapan terakhir kali tersimpan
                this.lastSaved = new Date().toISOString();
                return true;
            }
        } catch (error) {
            console.error("Error loading draft from localStorage:", error);
        }

        return false;
    },

    /**
     * Helper method to slugify text
     * @param {string} text - The text to slugify
     * @returns {string} The slugified text
     */
    slugify(text) {
        return slugify(text);
    },
};
