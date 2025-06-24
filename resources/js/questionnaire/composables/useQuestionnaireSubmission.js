/**
 * @fileoverview Composable for managing questionnaire submission
 *
 * This composable handles the submission of questionnaire answers to the server,
 * including processing the answers before submission.
 */

/**
 * Manages submission for a questionnaire
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.questionnaire - The questionnaire data object
 * @param {Object} options.state - The questionnaire state
 * @param {Object} options.state.answers - The answers ref
 * @param {Object} options.state.isSubmitting - The submitting state ref
 * @param {Boolean} options.isPreview - Whether the questionnaire is in preview mode
 * @param {Function} options.validateSection - Function to validate the current section
 * @param {Function} options.showThankYou - Function to show the thank you screen
 * @returns {Object} Submission methods
 */
export default function useQuestionnaireSubmission(options) {
    const {
        questionnaire,
        state: { answers, isSubmitting },
        isPreview,
        validateSection,
        showThankYou,
    } = options;

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
        if (!validateSection()) {
            return;
        }

        isSubmitting.value = true;

        if (isPreview) {
            // If in preview mode, just show the thank you screen
            setTimeout(() => {
                showThankYou();
                isSubmitting.value = false;
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
            showThankYou();

            // Dispatch event for the parent to know submission was successful
            document.dispatchEvent(new CustomEvent("questionnaire-submitted"));
        } catch (error) {
            console.error("Error submitting questionnaire:", error);
            alert(
                "Terjadi kesalahan saat mengirim jawaban. Silakan coba lagi."
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    return {
        submitQuestionnaire,
        processAnswersBeforeSubmit,
    };
}
