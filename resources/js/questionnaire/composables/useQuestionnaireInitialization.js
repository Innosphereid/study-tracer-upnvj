/**
 * @fileoverview Composable for questionnaire initialization
 *
 * This composable handles the initialization of the questionnaire,
 * including normalizing question data and setting up default answers.
 */

/**
 * Manages initialization for a questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @param {Object} options.state - The questionnaire state
 * @param {Object} options.state.answers - The answers ref
 * @param {Object} options.state.debug - Debug mode ref
 * @returns {Object} Initialization methods
 */
export default function useQuestionnaireInitialization(options) {
    const { questionnaire, state } = options;
    const { answers, debug } = state;

    /**
     * Initializes default values for a question based on its type
     *
     * @param {Object} question - The question object
     */
    const initializeQuestionAnswer = (question) => {
        // Set default empty values based on question type
        switch (question.type.toLowerCase()) {
            case "short-text":
            case "long-text":
            case "text":
            case "textarea":
                answers.value[question.id] = "";
                break;

            case "radio":
                answers.value[question.id] = {
                    value: "",
                    otherText: "",
                };
                break;

            case "checkbox":
                answers.value[question.id] = {
                    values: [],
                    otherText: "",
                };
                break;

            case "date":
                answers.value[question.id] = "";
                break;

            case "dropdown":
                answers.value[question.id] = {
                    value: "",
                    otherText: "",
                };
                break;

            case "email":
                answers.value[question.id] = "";
                break;

            case "file-upload":
            case "file":
                answers.value[question.id] = {
                    files: [],
                    fileUrls: [],
                };
                break;

            case "likert":
                if (question.settings && question.settings.statements) {
                    const likertValues = {};
                    question.settings.statements.forEach((statement) => {
                        likertValues[statement.id] = null;
                    });
                    answers.value[question.id] = likertValues;
                } else {
                    answers.value[question.id] = {};
                }
                break;

            case "matrix":
                if (question.settings && question.settings.rows) {
                    const matrixValues = {};
                    question.settings.rows.forEach((row) => {
                        matrixValues[row.id] = null;
                    });
                    answers.value[question.id] = matrixValues;
                } else {
                    answers.value[question.id] = {};
                }
                break;

            case "number":
                answers.value[question.id] = null;
                break;

            case "phone":
                answers.value[question.id] = "";
                break;

            case "ranking":
                if (question.options && question.options.length) {
                    answers.value[question.id] = question.options.map(
                        (option) => ({
                            id: option.id || option.value,
                            label: option.label,
                            value: option.value,
                            order: option.order,
                        })
                    );
                } else {
                    answers.value[question.id] = [];
                }
                break;

            case "rating":
                answers.value[question.id] = null;
                break;

            case "slider":
                // Start with middle value or min
                if (question.settings) {
                    const min = question.settings.min || 0;
                    const max = question.settings.max || 100;
                    answers.value[question.id] = min;
                } else {
                    answers.value[question.id] = 0;
                }
                break;

            case "yes-no":
            case "yesno":
                answers.value[question.id] = null;
                break;

            default:
                answers.value[question.id] = null;
        }
    };

    /**
     * Normalizes a question's metadata and properties
     *
     * @param {Object} question - The question object
     * @param {Number} questionIndex - The index of the question
     */
    const normalizeQuestion = (question, questionIndex) => {
        // Debug logging
        if (debug.value) {
            console.log(`Question ${questionIndex + 1}:`, question);
        }

        // Ensure question has type field (might be in settings or question_type)
        if (!question.type) {
            // Check if it's in question_type field (from database model)
            if (question.question_type) {
                question.type = question.question_type;
                if (debug.value) {
                    console.log(
                        `Set question type from question_type: ${question.type}`
                    );
                }
            } else if (question.settings) {
                // Try to extract from settings
                try {
                    let settingsObj = question.settings;

                    // If settings is a string, try to parse it
                    if (typeof question.settings === "string") {
                        settingsObj = JSON.parse(question.settings);
                    }

                    if (settingsObj.type) {
                        question.type = settingsObj.type;
                        if (debug.value) {
                            console.log(
                                `Set question type from settings: ${question.type}`
                            );
                        }
                    }
                } catch (e) {
                    console.error("Failed to parse question settings:", e);
                }
            }
        }

        // Set a default type if still missing
        if (!question.type) {
            question.type = "short-text";
            console.warn(
                `Question ${
                    questionIndex + 1
                } has no type, defaulting to short-text`
            );
        }

        // Parse settings if it's a string
        if (question.settings && typeof question.settings === "string") {
            try {
                question.settings = JSON.parse(question.settings);
                if (debug.value) {
                    console.log(
                        `Parsed settings for question ${questionIndex + 1}`
                    );
                }
            } catch (e) {
                console.error(
                    `Failed to parse settings for question ${
                        questionIndex + 1
                    }:`,
                    e
                );
                question.settings = {}; // Set to empty object if parse fails
            }
        }

        // Ensure text field exists (may be stored in title)
        if (!question.text && question.title) {
            question.text = question.title;
            if (debug.value) {
                console.log(`Set question text from title: ${question.text}`);
            }
        }

        // Ensure helpText field exists (may be stored in description)
        if (!question.helpText && question.description) {
            question.helpText = question.description;
            if (debug.value) {
                console.log(
                    `Set question helpText from description: ${question.helpText}`
                );
            }
        }

        // Ensure required field exists (may be stored in is_required)
        if (
            question.required === undefined &&
            question.is_required !== undefined
        ) {
            question.required = question.is_required;
            if (debug.value) {
                console.log(
                    `Set question required from is_required: ${question.required}`
                );
            }
        }

        // Initialize answer after normalization
        initializeQuestionAnswer(question);
    };

    /**
     * Initializes the questionnaire and sets up default answers
     */
    const initializeQuestionnaire = () => {
        console.log(
            "Initializing Alumni Questionnaire with questionnaire:",
            questionnaire
        );

        // Transform question data if needed
        if (questionnaire.sections) {
            questionnaire.sections.forEach((section, sectionIndex) => {
                console.log(
                    `Processing section ${sectionIndex + 1}:`,
                    section.title
                );

                if (section.questions) {
                    section.questions.forEach((question, questionIndex) => {
                        normalizeQuestion(question, questionIndex);
                    });
                } else {
                    console.error(
                        `Section ${sectionIndex + 1} (${
                            section.title
                        }) has no questions array!`
                    );
                }
            });
        } else {
            console.error("Questionnaire has no sections array!");
        }
    };

    return {
        initializeQuestionnaire,
        normalizeQuestion,
        initializeQuestionAnswer,
    };
}
