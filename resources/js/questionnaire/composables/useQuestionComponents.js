/**
 * @fileoverview Composable for managing question component types
 *
 * This composable handles the mapping between question types and their
 * corresponding Vue components. It standardizes type names and provides
 * detailed logging for debugging.
 */

// Question components
import ShortTextQuestion from "../components/alumni/questions/ShortTextQuestion.vue";
import LongTextQuestion from "../components/alumni/questions/LongTextQuestion.vue";
import RadioQuestion from "../components/alumni/questions/RadioQuestion.vue";
import CheckboxQuestion from "../components/alumni/questions/CheckboxQuestion.vue";
import DateQuestion from "../components/alumni/questions/DateQuestion.vue";
import DropdownQuestion from "../components/alumni/questions/DropdownQuestion.vue";
import EmailQuestion from "../components/alumni/questions/EmailQuestion.vue";
import FileUploadQuestion from "../components/alumni/questions/FileUploadQuestion.vue";
import LikertQuestion from "../components/alumni/questions/LikertQuestion.vue";
import MatrixQuestion from "../components/alumni/questions/MatrixQuestion.vue";
import NumberQuestion from "../components/alumni/questions/NumberQuestion.vue";
import PhoneQuestion from "../components/alumni/questions/PhoneQuestion.vue";
import RankingQuestion from "../components/alumni/questions/RankingQuestion.vue";
import RatingQuestion from "../components/alumni/questions/RatingQuestion.vue";
import SliderQuestion from "../components/alumni/questions/SliderQuestion.vue";
import YesNoQuestion from "../components/alumni/questions/YesNoQuestion.vue";

/**
 * Composable that provides question component selection functionality
 *
 * @param {Object} options - Options for the composable
 * @param {Boolean} options.debug - Whether to enable debug logging
 * @returns {Object} Functions related to question components
 */
export default function useQuestionComponents(options = {}) {
    const { debug = false } = options;

    /**
     * Type mapping from database values to component types
     */
    const typeMapping = {
        text: "short-text",
        textarea: "long-text",
        checkbox: "checkbox",
        radio: "radio",
        date: "date",
        dropdown: "dropdown",
        email: "email",
        file: "file-upload",
        likert: "likert",
        matrix: "matrix",
        number: "number",
        phone: "phone",
        ranking: "ranking",
        rating: "rating",
        slider: "slider",
        "yes-no": "yes-no",
        yesno: "yes-no",
    };

    /**
     * Component map for all question types
     */
    const componentMap = {
        "short-text": ShortTextQuestion,
        "long-text": LongTextQuestion,
        radio: RadioQuestion,
        checkbox: CheckboxQuestion,
        date: DateQuestion,
        dropdown: DropdownQuestion,
        email: EmailQuestion,
        "file-upload": FileUploadQuestion,
        likert: LikertQuestion,
        matrix: MatrixQuestion,
        number: NumberQuestion,
        phone: PhoneQuestion,
        ranking: RankingQuestion,
        rating: RatingQuestion,
        slider: SliderQuestion,
        "yes-no": YesNoQuestion,
    };

    /**
     * Gets the appropriate component for a question type
     *
     * @param {String} type - The raw question type from database
     * @returns {Object} Vue component for the question type
     */
    const getQuestionComponent = (type) => {
        if (debug) {
            console.log("Getting component for type:", type);
        }

        // Normalize type dari database
        // Beberapa data mungkin menggunakan question_type atau type
        let normalizedType = type ? type.toLowerCase() : "short-text";

        // Debugging khusus untuk tipe matrix
        if (normalizedType === "matrix" && debug) {
            console.log("Matrix question detected - debugging info added");
        }

        // If the type is in our mapping, use the normalized version
        if (typeMapping[normalizedType]) {
            normalizedType = typeMapping[normalizedType];
            if (debug) {
                console.log(
                    `Normalized question type from ${type} to ${normalizedType}`
                );
            }
        }

        if (!componentMap[normalizedType] && debug) {
            console.warn(
                `Unknown question type: ${type} (normalized to ${normalizedType}), falling back to short-text`
            );
        }

        return componentMap[normalizedType] || ShortTextQuestion; // Fallback to short text if type not found
    };

    return {
        getQuestionComponent,
    };
}
