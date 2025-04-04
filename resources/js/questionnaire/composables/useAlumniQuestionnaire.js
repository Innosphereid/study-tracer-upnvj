/**
 * @fileoverview Composable for managing alumni questionnaire state and logic
 *
 * This composable extracts the state management and business logic for the
 * alumni questionnaire, following the principle of separation of concerns.
 * It handles questionnaire progression, validation, answer processing, and submission.
 */

import { ref, computed, watch } from "vue";

/**
 * Manages the state and logic for the alumni questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @param {Boolean} options.isPreview - Whether the questionnaire is in preview mode
 * @returns {Object} The questionnaire state and methods
 */
export default function useAlumniQuestionnaire(options) {
    const { questionnaire, isPreview = false } = options;

    // Debug flag - hanya diaktifkan jika eksplisit diminta
    const debug = ref(window.location.href.includes("debug=true"));

    // UI state
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const currentStep = ref("welcome");
    const currentSectionIndex = ref(0);
    const answers = ref({});
    const errors = ref({});
    const validationState = ref({});

    // Computed properties
    const totalSections = computed(() => questionnaire.sections.length);

    const currentSection = computed(() => {
        if (
            currentSectionIndex.value >= 0 &&
            currentSectionIndex.value < totalSections.value
        ) {
            return questionnaire.sections[currentSectionIndex.value];
        }
        return null;
    });

    const progress = computed(() => {
        if (totalSections.value === 0) return 0;
        return ((currentSectionIndex.value + 1) / totalSections.value) * 100;
    });

    // Welcome Screen computed properties with fallbacks
    const welcomeScreenTitle = computed(() => {
        // Check various possible locations for the welcome screen title
        if (questionnaire.welcomeScreen && questionnaire.welcomeScreen.title) {
            return questionnaire.welcomeScreen.title;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.title
        ) {
            return questionnaire.settings.welcomeScreen.title;
        }

        return questionnaire.title || "Selamat Datang di Kuesioner Alumni";
    });

    const welcomeScreenSubtitle = computed(() => {
        if (
            questionnaire.welcomeScreen &&
            questionnaire.welcomeScreen.subtitle
        ) {
            return questionnaire.welcomeScreen.subtitle;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.subtitle
        ) {
            return questionnaire.settings.welcomeScreen.subtitle;
        }

        return "TraceStudy UPNVJ";
    });

    const welcomeScreenDescription = computed(() => {
        if (
            questionnaire.welcomeScreen &&
            questionnaire.welcomeScreen.description
        ) {
            return questionnaire.welcomeScreen.description;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.welcomeScreen &&
            questionnaire.settings.welcomeScreen.description
        ) {
            return questionnaire.settings.welcomeScreen.description;
        }

        return "Terima kasih telah berpartisipasi dalam kuesioner ini. Jawaban Anda sangat berarti bagi kami.";
    });

    // Thank You Screen computed properties with fallbacks
    const thankYouScreenTitle = computed(() => {
        if (
            questionnaire.thankYouScreen &&
            questionnaire.thankYouScreen.title
        ) {
            return questionnaire.thankYouScreen.title;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.thankYouScreen &&
            questionnaire.settings.thankYouScreen.title
        ) {
            return questionnaire.settings.thankYouScreen.title;
        }

        return "Terima Kasih!";
    });

    const thankYouScreenDescription = computed(() => {
        if (
            questionnaire.thankYouScreen &&
            questionnaire.thankYouScreen.description
        ) {
            return questionnaire.thankYouScreen.description;
        }

        if (
            questionnaire.settings &&
            questionnaire.settings.thankYouScreen &&
            questionnaire.settings.thankYouScreen.description
        ) {
            return questionnaire.settings.thankYouScreen.description;
        }

        return "Jawaban Anda telah berhasil disimpan. Terima kasih atas partisipasi Anda dalam kuesioner ini.";
    });

    /**
     * Starts the questionnaire and shows the first section
     */
    const startQuestionnaire = () => {
        currentStep.value = "questions";
        currentSectionIndex.value = 0;

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    /**
     * Moves to the previous section
     */
    const previousSection = () => {
        if (currentSectionIndex.value > 0) {
            currentSectionIndex.value--;

            // Scroll to top of new section
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    /**
     * Handles validation results for a question
     *
     * @param {Object} validationResult - The validation result object
     * @param {String} questionId - The ID of the question being validated
     */
    const handleValidation = (validationResult, questionId) => {
        validationState.value[questionId] = validationResult;

        if (!validationResult.isValid) {
            errors.value[questionId] = validationResult.errorMessage;
        } else {
            delete errors.value[questionId];
        }
    };

    /**
     * Validates all questions in the current section
     *
     * @returns {Boolean} Whether all questions in the current section are valid
     */
    const validateCurrentSection = () => {
        let isValid = true;

        // Check if current section exists
        if (!currentSection.value) return true;

        // Validate required questions in the current section
        currentSection.value.questions.forEach((question) => {
            if (question.required) {
                // Get validation result if available
                const validation = validationState.value[question.id];

                if (!validation || !validation.isValid) {
                    errors.value[question.id] = "Pertanyaan ini wajib dijawab.";
                    isValid = false;
                }
            }
        });

        return isValid;
    };

    /**
     * Moves to the next section if the current section is valid
     */
    const nextSection = () => {
        // Validate current section before proceeding
        if (!validateCurrentSection()) {
            // Scroll to first error
            const firstErrorElement = document.querySelector(".text-red-600");
            if (firstErrorElement) {
                firstErrorElement.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
            return;
        }

        if (currentSectionIndex.value < totalSections.value - 1) {
            currentSectionIndex.value++;

            // Scroll to top of new section
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    };

    /**
     * Processes answers before submitting to the server
     *
     * @param {Object} answersData - The raw answers data
     * @returns {Object} The processed answers
     */
    const processAnswersBeforeSubmit = (answersData) => {
        const processedAnswers = { ...answersData };

        // Cari semua pertanyaan dalam seluruh section
        const allQuestions = [];
        if (questionnaire.sections) {
            questionnaire.sections.forEach((section) => {
                if (section.questions) {
                    allQuestions.push(...section.questions);
                }
            });
        }

        // Proses jawaban berdasarkan tipe pertanyaan
        allQuestions.forEach((question) => {
            const questionId = question.id;
            const answer = processedAnswers[questionId];

            // Khusus untuk pertanyaan tipe radio
            if (
                question.type?.toLowerCase() === "radio" &&
                answer &&
                typeof answer === "object"
            ) {
                if (answer.value === "other" && answer.otherText) {
                    // Jika "other" dipilih, gunakan otherText sebagai jawaban
                    processedAnswers[questionId] = answer.otherText;
                } else {
                    // Gunakan hanya nilai "value" saja
                    processedAnswers[questionId] = answer.value;
                }
            }

            // Khusus untuk pertanyaan tipe checkbox
            if (
                question.type?.toLowerCase() === "checkbox" &&
                answer &&
                typeof answer === "object" &&
                Array.isArray(answer.values) &&
                answer.values.length === 0 &&
                !question.required
            ) {
                // Gunakan string literal "null" untuk memastikan nilai yang benar disimpan di kolom answer_values
                processedAnswers[questionId] = "null";
            }
        });

        return processedAnswers;
    };

    /**
     * Submits the questionnaire answers to the server
     */
    const submitQuestionnaire = async () => {
        // Validate final section before submitting
        if (!validateCurrentSection()) {
            return;
        }

        isSubmitting.value = true;

        if (isPreview) {
            // If in preview mode, just show the thank you screen
            setTimeout(() => {
                currentStep.value = "thankYou";
                isSubmitting.value = false;
                window.scrollTo({ top: 0, behavior: "smooth" });
            }, 1000);
            return;
        }

        try {
            // Prepare submission data
            const submissionData = {
                slug: questionnaire.slug || questionnaire.id,
                answers: processAnswersBeforeSubmit(answers.value),
            };

            // Send data to server
            const response = await fetch("/kuesioner/submit", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(submissionData),
            });

            if (!response.ok) {
                throw new Error("Failed to submit questionnaire");
            }

            // Show thank you screen
            currentStep.value = "thankYou";

            // Dispatch event for the parent to know submission was successful
            document.dispatchEvent(new CustomEvent("questionnaire-submitted"));

            // Scroll to top
            window.scrollTo({ top: 0, behavior: "smooth" });
        } catch (error) {
            console.error("Error submitting questionnaire:", error);
            alert(
                "Terjadi kesalahan saat mengirim jawaban. Silakan coba lagi."
            );
        } finally {
            isSubmitting.value = false;
        }
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
                        // Debug
                        if (debug.value) {
                            console.log(
                                `Question ${questionIndex + 1}:`,
                                question
                            );
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
                                        settingsObj = JSON.parse(
                                            question.settings
                                        );
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
                                    console.error(
                                        "Failed to parse question settings:",
                                        e
                                    );
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
                        if (
                            question.settings &&
                            typeof question.settings === "string"
                        ) {
                            try {
                                question.settings = JSON.parse(
                                    question.settings
                                );
                                if (debug.value) {
                                    console.log(
                                        `Parsed settings for question ${
                                            questionIndex + 1
                                        }`
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
                                console.log(
                                    `Set question text from title: ${question.text}`
                                );
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

                        // Initialize answers object based on question types
                        initializeQuestionAnswer(question);
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

    return {
        // State
        isLoading,
        isSubmitting,
        currentStep,
        currentSectionIndex,
        answers,
        errors,
        validationState,

        // Computed
        totalSections,
        currentSection,
        progress,
        welcomeScreenTitle,
        welcomeScreenSubtitle,
        welcomeScreenDescription,
        thankYouScreenTitle,
        thankYouScreenDescription,

        // Methods
        startQuestionnaire,
        previousSection,
        nextSection,
        handleValidation,
        submitQuestionnaire,
        initializeQuestionnaire,
    };
}
