<template>
    <div class="question-screen p-8">
        <!-- Progress Indicator -->
        <div
            v-if="questionnaire.showProgressBar"
            class="mb-8 progress-container"
        >
            <div
                class="flex items-center justify-between text-xs text-gray-500 mb-1"
            >
                <span>{{ Math.round(progress) }}% Selesai</span>
                <span v-if="questionnaire.showPageNumbers">
                    Halaman {{ getCurrentSectionNumber() }} dari
                    {{ getTotalSections() }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                <div
                    class="bg-indigo-600 h-2.5 rounded-full progress-bar-animated transition-all duration-500 ease-out"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>
        </div>

        <!-- Current Section Content -->
        <div v-if="currentSection" class="space-y-8">
            <!-- Section Header -->
            <div
                class="section-header"
                :class="{ 'animate-fade-in': animateSection }"
            >
                <h2 class="text-2xl font-semibold text-gray-900">
                    {{ currentSection.title }}
                </h2>
                <p v-if="currentSection.description" class="mt-2 text-gray-600">
                    {{ currentSection.description }}
                </p>
            </div>

            <!-- Validation Error Message - Show if any required field is missing -->
            <div
                v-if="Object.keys(errors).length > 0"
                class="p-4 bg-red-50 border border-red-200 rounded-md text-red-700 mb-4 animate__animated animate__headShake"
            >
                <div class="flex">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 mr-2 text-red-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div>
                        <p class="font-medium">
                            Ada pertanyaan wajib yang belum dijawab
                        </p>
                        <p class="text-sm mt-1">
                            Silakan lengkapi semua pertanyaan yang ditandai
                            dengan tanda bintang (*)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="space-y-6 questions-container">
                <TransitionGroup name="question">
                    <div
                        v-for="(question, index) in paginatedQuestions"
                        :key="question.id"
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300"
                        :class="{
                            'border-red-300 bg-red-50': errors[question.id],
                        }"
                    >
                        <!-- Question Number & Text -->
                        <div class="mb-4">
                            <div class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-800 text-sm font-medium mr-3"
                                    :class="{
                                        'bg-red-100 text-red-800':
                                            errors[question.id],
                                    }"
                                >
                                    {{ getQuestionDisplayNumber(index) }}
                                </span>
                                <div>
                                    <h3
                                        class="text-lg font-medium text-gray-900 mb-1"
                                        :class="{
                                            'text-red-700': errors[question.id],
                                        }"
                                    >
                                        {{ question.text }}
                                        <span
                                            v-if="question.required"
                                            class="text-red-500 ml-1"
                                            >*</span
                                        >
                                    </h3>
                                    <p
                                        v-if="question.helpText"
                                        class="text-sm text-gray-500"
                                    >
                                        {{ question.helpText }}
                                    </p>

                                    <!-- Error message -->
                                    <p
                                        v-if="errors[question.id]"
                                        class="mt-1 text-sm text-red-600 font-medium"
                                    >
                                        {{ errors[question.id] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Question Component -->
                        <div class="pl-9">
                            <component
                                :is="getQuestionComponent(question.type)"
                                :question="normalizeQuestionData(question)"
                                :modelValue="
                                    getFormattedAnswer(
                                        question.id,
                                        question.type
                                    )
                                "
                                @update:modelValue="
                                    answers[question.id] = $event
                                "
                                :error="errors[question.id]"
                                class="question-input-component"
                                :class="{
                                    'error-highlight': errors[question.id],
                                }"
                            ></component>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Question Pagination - Only show if section has pagination -->
            <div
                v-if="shouldShowPagination"
                class="flex justify-center mt-6 space-x-2"
            >
                <button
                    v-for="page in totalPages"
                    :key="`page-${page}`"
                    class="px-3 py-1 rounded-md text-sm"
                    :class="
                        page === currentPage
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                    "
                    @click="currentPage = page"
                >
                    {{ page }}
                </button>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div
            class="mt-10 flex justify-between items-center navigation-container"
            :class="{ 'animate-fade-in-delayed': animateSection }"
        >
            <!-- Previous button - Show if on a page beyond first OR in previous section -->
            <button
                v-if="canGoBack"
                type="button"
                class="btn-prev inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                @click="goBack"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="-ml-1 mr-2 h-5 w-5 text-gray-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Sebelumnya
            </button>
            <div v-else></div>

            <!-- Next/Finish button - Show "Next" if more pages or sections, "Finish" if on last page of last section -->
            <button
                v-if="hasMoreContent"
                type="button"
                class="btn-next inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                @click="goNext"
            >
                Selanjutnya
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="ml-2 h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>

            <button
                v-else
                type="button"
                class="btn-submit inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                @click="finishQuestionnaire"
            >
                Selesai
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="ml-2 h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

// Import question components
// Note: Assuming these components are available from your project
// We'll dynamically import them instead of hardcoding to allow for flexibility
const questionComponents = import.meta.glob("../questions/*.vue", {
    eager: true,
});

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
    currentSection: {
        type: Object,
        required: true,
    },
    progress: {
        type: Number,
        default: 0,
    },
    answers: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["next", "previous", "finish"]);

// UI State
const animateSection = ref(false);
const currentPage = ref(1);

// Get questions per page setting from section settings
const questionsPerPage = computed(() => {
    if (props.currentSection?.settings?.questionsPerPage) {
        const value = props.currentSection.settings.questionsPerPage;
        return value === "all" ? null : parseInt(value);
    }
    return null; // No pagination if setting not found
});

// Check if we should show pagination
const shouldShowPagination = computed(() => {
    return (
        !!questionsPerPage.value &&
        props.currentSection?.questions?.length > questionsPerPage.value
    );
});

// Calculate total number of pages
const totalPages = computed(() => {
    if (!questionsPerPage.value || !props.currentSection?.questions) {
        return 1;
    }
    return Math.ceil(
        props.currentSection.questions.length / questionsPerPage.value
    );
});

// Paginate questions based on settings
const paginatedQuestions = computed(() => {
    const allQuestions = props.currentSection?.questions || [];
    if (!questionsPerPage.value) {
        return allQuestions; // Return all if no pagination
    }

    const startIndex = (currentPage.value - 1) * questionsPerPage.value;
    const endIndex = startIndex + questionsPerPage.value;
    return allQuestions.slice(startIndex, endIndex);
});

// Calculate question display number (considering pagination)
const getQuestionDisplayNumber = (indexOnPage) => {
    if (!questionsPerPage.value) {
        return indexOnPage + 1; // No pagination, just use index
    }

    // Calculate actual question index based on pagination
    return (currentPage.value - 1) * questionsPerPage.value + indexOnPage + 1;
};

// Can navigate back (previous page or section)
const canGoBack = computed(() => {
    if (currentPage.value > 1) {
        return true; // Can go to previous page within section
    }
    return hasPreviousSection(); // Or to previous section
});

// Has more content (more pages in section or more sections)
const hasMoreContent = computed(() => {
    if (currentPage.value < totalPages.value) {
        return true; // Has more pages within section
    }
    return hasNextSection(); // Or has more sections
});

// Navigation methods
const goBack = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    } else {
        emit("previous");
    }
};

const validateCurrentPageQuestions = () => {
    let isValid = true;
    const questionsOnCurrentPage = paginatedQuestions.value;

    // Check each question on the current page
    for (const question of questionsOnCurrentPage) {
        if (question.required) {
            // Check if the answer exists and is valid
            const answer = props.answers[question.id];
            let questionValid = false;

            switch (question.type) {
                case "short-text":
                case "long-text":
                case "email":
                    questionValid = !!answer?.trim();
                    break;

                case "radio":
                case "dropdown":
                    questionValid = !!answer?.value;
                    // Also consider "none" and "other" as valid answers
                    if (!questionValid && answer?.value === "none") {
                        questionValid = true;
                    }
                    if (
                        !questionValid &&
                        answer?.value === "other" &&
                        answer?.otherText?.trim()
                    ) {
                        questionValid = true;
                    }
                    break;

                case "checkbox":
                    questionValid =
                        Array.isArray(answer?.values) &&
                        answer.values.length > 0;
                    // For checkbox, "none" is also a valid answer
                    if (!questionValid && answer?.none) {
                        questionValid = true;
                    }
                    break;

                case "rating":
                    questionValid =
                        answer > 0 ||
                        (typeof answer === "string" &&
                            answer !== "" &&
                            parseInt(answer, 10) > 0);
                    break;

                default:
                    // Default validation just checks if there's any value
                    questionValid = !!answer;
            }

            if (!questionValid) {
                props.errors[question.id] = "Pertanyaan ini wajib dijawab.";
                isValid = false;
            }
        }
    }

    return isValid;
};

const goNext = () => {
    // Validate current page before proceeding
    if (!validateCurrentPageQuestions()) {
        // Show error message and prevent navigation
        const firstErrorElement = document.querySelector(".error-highlight");
        if (firstErrorElement) {
            firstErrorElement.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }
        return;
    }

    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    } else {
        // When going to the next section, validate all questions in the current section
        // This is needed because we might have skipped some questions due to pagination

        // Clear existing errors first
        for (const questionId in props.errors) {
            delete props.errors[questionId];
        }

        // Check all questions in the section
        let allValid = true;
        if (props.currentSection?.questions) {
            for (const question of props.currentSection.questions) {
                if (question.required) {
                    const answer = props.answers[question.id];
                    let questionValid = false;

                    switch (question.type) {
                        case "short-text":
                        case "long-text":
                        case "email":
                            questionValid = !!answer?.trim();
                            break;

                        case "radio":
                        case "dropdown":
                            questionValid = !!answer?.value;
                            break;

                        case "checkbox":
                            questionValid =
                                Array.isArray(answer?.values) &&
                                answer.values.length > 0;
                            break;

                        case "rating":
                            questionValid =
                                answer > 0 ||
                                (typeof answer === "string" &&
                                    answer !== "" &&
                                    parseInt(answer, 10) > 0);
                            break;

                        case "matrix":
                            if (question.matrixType === "radio") {
                                // For radio matrix, check if each row has at least one selected option
                                const responses = answer?.responses || {};
                                questionValid =
                                    question.rows &&
                                    question.rows.every(
                                        (row) => !!responses[row.id]
                                    );
                            } else if (question.matrixType === "checkbox") {
                                // For checkbox matrix, check if there's at least one checked box
                                const checkboxResponses =
                                    answer?.checkboxResponses || {};
                                questionValid =
                                    Object.keys(checkboxResponses).length > 0;
                            } else {
                                questionValid = !!answer;
                            }
                            break;

                        default:
                            questionValid = !!answer;
                    }

                    if (!questionValid) {
                        props.errors[question.id] =
                            "Pertanyaan ini wajib dijawab.";
                        allValid = false;
                    }
                }
            }
        }

        // If validation passes, go to next section
        if (allValid) {
            emit("next");
            // Reset page counter when moving to next section
            currentPage.value = 1;
        } else {
            // Go to the page containing the first error
            const invalidQuestionIds = Object.keys(props.errors);
            if (invalidQuestionIds.length > 0) {
                const firstInvalidQuestion =
                    props.currentSection.questions.find(
                        (q) => q.id === invalidQuestionIds[0]
                    );
                const questionIndex =
                    props.currentSection.questions.indexOf(
                        firstInvalidQuestion
                    );

                if (questionIndex !== -1 && questionsPerPage.value) {
                    const pageWithError =
                        Math.floor(questionIndex / questionsPerPage.value) + 1;
                    currentPage.value = pageWithError;

                    // After page change, scroll to the error
                    setTimeout(() => {
                        const errorElement =
                            document.querySelector(".error-highlight");
                        if (errorElement) {
                            errorElement.scrollIntoView({
                                behavior: "smooth",
                                block: "center",
                            });
                        }
                    }, 100);
                }
            }
        }
    }
};

// Computed methods for section navigation
const getCurrentSectionNumber = () => {
    const index = props.questionnaire.sections.findIndex(
        (section) => section.id === props.currentSection.id
    );
    return index + 1;
};

const getTotalSections = () => {
    return props.questionnaire.sections.length;
};

const hasPreviousSection = () => {
    const index = props.questionnaire.sections.findIndex(
        (section) => section.id === props.currentSection.id
    );
    return index > 0;
};

const hasNextSection = () => {
    const index = props.questionnaire.sections.findIndex(
        (section) => section.id === props.currentSection.id
    );
    return index < props.questionnaire.sections.length - 1;
};

// Helper to dynamically get the correct component for question type
const getQuestionComponent = (type) => {
    console.log("Trying to get component for question type:", type);

    // Map backend question types to component names
    // This is critical - the backend stores types like 'text' but components are named like 'ShortTextQuestion'
    const componentMap = {
        // Frontend types (from builder)
        "short-text": "ShortTextQuestion",
        "long-text": "LongTextQuestion",
        radio: "RadioQuestion",
        checkbox: "CheckboxQuestion",
        dropdown: "DropdownQuestion",
        rating: "RatingQuestion",
        "yes-no": "YesNoQuestion",
        email: "EmailQuestion",
        phone: "PhoneQuestion",
        number: "NumberQuestion",
        date: "DateQuestion",
        "file-upload": "FileUploadQuestion",
        slider: "SliderQuestion",
        matrix: "MatrixQuestion",
        ranking: "RankingQuestion",
        likert: "LikertQuestion",

        // Backend types (from database)
        text: "ShortTextQuestion",
        textarea: "LongTextQuestion",
        file: "FileUploadQuestion",
    };

    // Get the component name based on the type
    const componentName = componentMap[type] || "ShortTextQuestion";
    console.log("Mapped to component name:", componentName);

    // Try to find the component in the dynamically imported modules
    for (const path in questionComponents) {
        if (path.includes(componentName)) {
            console.log("Found component at path:", path);
            return questionComponents[path].default;
        }
    }

    // Fallback to a default component if none found
    console.warn(`No component found for type ${type}, using default`);
    return questionComponents[Object.keys(questionComponents)[0]].default;
};

// Ensure question answers use correct types before rendering components
const normalizeQuestionData = (question) => {
    // Make a copy to avoid modifying the original
    const questionData = { ...question };

    // If settings exist and it's a string, parse it
    if (questionData.settings && typeof questionData.settings === "string") {
        try {
            questionData.settings = JSON.parse(questionData.settings);
            console.log(
                "Parsed settings for question",
                questionData.id,
                questionData.settings
            );

            // For rating questions, ensure labels are properly extracted
            if (
                questionData.type === "rating" &&
                questionData.settings.labels &&
                !questionData.labels
            ) {
                questionData.labels = questionData.settings.labels;
                console.log(
                    "Extracted labels for rating question",
                    questionData.id,
                    questionData.labels
                );
            }
        } catch (e) {
            console.error(
                "Failed to parse settings JSON for question",
                questionData.id,
                e
            );
            questionData.settings = {};
        }
    }

    // If settings is an object, apply all properties to the question
    if (questionData.settings && typeof questionData.settings === "object") {
        // Apply settings properties to question root for direct access
        Object.entries(questionData.settings).forEach(([key, value]) => {
            // Only set if the property doesn't already exist
            if (questionData[key] === undefined) {
                questionData[key] = value;
            }
        });

        // Ensure primary fields are set
        if (
            questionData.settings.required !== undefined &&
            questionData.required === undefined
        ) {
            questionData.required = Boolean(questionData.settings.required);
        }

        if (
            questionData.settings.text !== undefined &&
            questionData.text === undefined
        ) {
            questionData.text = questionData.settings.text;
        }

        if (
            questionData.settings.helpText !== undefined &&
            questionData.helpText === undefined
        ) {
            questionData.helpText = questionData.settings.helpText;
        }

        // Handle type-specific properties
        if (questionData.type) {
            switch (questionData.type) {
                case "radio":
                case "checkbox":
                case "dropdown":
                    // Ensure options are available
                    if (
                        !questionData.options &&
                        questionData.settings.options
                    ) {
                        questionData.options = questionData.settings.options;
                    } else if (!questionData.options) {
                        // Default empty options array if none exists
                        questionData.options = [];
                    }

                    // Normalize options format - ensure each option has id, text, and value
                    if (Array.isArray(questionData.options)) {
                        questionData.options = questionData.options.map(
                            (option, index) => {
                                if (typeof option === "string") {
                                    return {
                                        id: `option_${option}`,
                                        text: option,
                                        value: option,
                                    };
                                }

                                const normalizedOption = { ...option };

                                // Ensure option has an id
                                if (!normalizedOption.id) {
                                    normalizedOption.id = `option-${index}`;
                                }

                                // Ensure option has text - fallback to label or value
                                if (!normalizedOption.text) {
                                    normalizedOption.text =
                                        normalizedOption.label ||
                                        normalizedOption.value ||
                                        `Option ${index + 1}`;
                                }

                                // Set value to match text content instead of generic option_N values
                                if (
                                    !normalizedOption.value ||
                                    normalizedOption.value.startsWith("option_")
                                ) {
                                    normalizedOption.value =
                                        normalizedOption.text;
                                }

                                return normalizedOption;
                            }
                        );

                        // Apply options ordering based on optionsOrder setting
                        if (
                            questionData.optionsOrder === "desc" ||
                            (questionData.settings &&
                                questionData.settings.optionsOrder === "desc")
                        ) {
                            // Sort options in descending order (Z to A)
                            console.log(
                                `Sorting options for question ${questionData.id} in descending order`
                            );
                            questionData.options = [
                                ...questionData.options,
                            ].sort((a, b) => {
                                // Compare text values for sorting
                                return b.text.localeCompare(a.text);
                            });
                        }
                    }

                    // Handle allowOther flag
                    if (questionData.settings.allowOther !== undefined) {
                        questionData.allowOther = Boolean(
                            questionData.settings.allowOther
                        );
                    }

                    // Handle allowNone flag
                    if (questionData.settings.allowNone !== undefined) {
                        questionData.allowNone = Boolean(
                            questionData.settings.allowNone
                        );
                    }

                    // Copy other option-related settings
                    ["defaultValue"].forEach((prop) => {
                        if (
                            questionData.settings[prop] !== undefined &&
                            questionData[prop] === undefined
                        ) {
                            questionData[prop] = questionData.settings[prop];
                        }
                    });
                    break;

                case "matrix":
                    // Copy matrix-specific properties
                    ["matrixType", "rows", "columns"].forEach((prop) => {
                        if (
                            questionData.settings[prop] !== undefined &&
                            questionData[prop] === undefined
                        ) {
                            questionData[prop] = questionData.settings[prop];
                        }
                    });

                    // Normalize rows format
                    if (Array.isArray(questionData.rows)) {
                        questionData.rows = questionData.rows.map(
                            (row, index) => {
                                if (typeof row === "string") {
                                    return {
                                        id: `row_${row}`,
                                        text: row,
                                        value: row,
                                    };
                                }

                                // Ensure row has value
                                if (!row.value) {
                                    row.value = row.text;
                                }

                                return row;
                            }
                        );

                        // Apply sorting if rowsOrder is set to 'desc'
                        if (questionData.rowsOrder === "desc") {
                            console.log(
                                `Sorting rows for matrix question ${questionData.id} in descending order`
                            );
                            questionData.rows.sort((a, b) =>
                                b.text.localeCompare(a.text)
                            );
                        }
                    }

                    // Normalize columns format
                    if (Array.isArray(questionData.columns)) {
                        questionData.columns = questionData.columns.map(
                            (column, index) => {
                                if (typeof column === "string") {
                                    return {
                                        id: `column_${column}`,
                                        text: column,
                                        value: column,
                                    };
                                }

                                // Ensure column has value
                                if (!column.value) {
                                    column.value = column.text;
                                }

                                return column;
                            }
                        );

                        // Apply sorting if columnsOrder is set to 'desc'
                        if (questionData.columnsOrder === "desc") {
                            console.log(
                                `Sorting columns for matrix question ${questionData.id} in descending order`
                            );
                            questionData.columns.sort((a, b) =>
                                b.text.localeCompare(a.text)
                            );
                        }
                    }
                    break;

                case "rating":
                    // Copy rating-specific properties
                    [
                        "maxRating",
                        "showValues",
                        "icon",
                        "defaultValue",
                        "labels",
                        "minRating",
                        "maxRatingValue",
                        "stepValue",
                    ].forEach((prop) => {
                        if (
                            questionData.settings[prop] !== undefined &&
                            questionData[prop] === undefined
                        ) {
                            questionData[prop] = questionData.settings[prop];
                        }
                    });

                    // Ensure maxRating is numeric
                    if (
                        questionData.maxRating &&
                        !Number.isInteger(questionData.maxRating)
                    ) {
                        questionData.maxRating =
                            parseInt(questionData.maxRating, 10) || 5;
                    } else if (!questionData.maxRating) {
                        questionData.maxRating = 5; // Default if not set
                    }
                    break;

                case "short-text":
                case "long-text":
                case "email":
                case "phone":
                case "number":
                    // Copy text input specific properties
                    [
                        "placeholder",
                        "defaultValue",
                        "minLength",
                        "maxLength",
                        "pattern",
                    ].forEach((prop) => {
                        if (
                            questionData.settings[prop] !== undefined &&
                            questionData[prop] === undefined
                        ) {
                            questionData[prop] = questionData.settings[prop];
                        }
                    });
                    break;

                case "date":
                    // Copy date-specific properties
                    ["format", "min", "max", "defaultValue"].forEach((prop) => {
                        if (
                            questionData.settings[prop] !== undefined &&
                            questionData[prop] === undefined
                        ) {
                            questionData[prop] = questionData.settings[prop];
                        }
                    });
                    break;
            }
        }
    }

    // For database question types, ensure we also check the frontend type
    if (questionData.settings && questionData.settings.type) {
        // This handles cases where the database stores 'text' but the actual component needs 'short-text'
        const frontendType = questionData.settings.type;
        questionData.frontendType = frontendType;
    }

    // Debug output to see the normalized data
    console.log("Normalized question data:", {
        id: questionData.id,
        type: questionData.type,
        options: questionData.options,
        allowOther: questionData.allowOther,
        allowNone: questionData.allowNone,
        optionsOrder: questionData.optionsOrder,
    });

    return questionData;
};

// Normalize answers based on question type to ensure consistent types
const getFormattedAnswer = (questionId, questionType) => {
    const answer = props.answers[questionId];

    // If no answer, return empty value based on type
    if (answer === undefined || answer === null) {
        return "";
    }

    // Format based on question type
    switch (questionType) {
        case "rating":
            // Ensure rating is a string for the component
            return typeof answer === "number" ? answer.toString() : answer;

        default:
            return answer;
    }
};

// Animation effect when the component mounts
onMounted(() => {
    // Delay the animation start slightly
    setTimeout(() => {
        animateSection.value = true;
    }, 100);
});

// Function to validate all questions in the current section
const validateAllQuestions = () => {
    // Clear existing errors first
    for (const questionId in props.errors) {
        delete props.errors[questionId];
    }

    // Check all questions in the section
    let isValid = true;
    if (props.currentSection?.questions) {
        for (const question of props.currentSection.questions) {
            if (question.required) {
                const answer = props.answers[question.id];
                let questionValid = false;

                switch (question.type) {
                    case "short-text":
                    case "long-text":
                    case "email":
                        questionValid = !!answer?.trim();
                        break;

                    case "radio":
                    case "dropdown":
                        questionValid = !!answer?.value;
                        // Also consider "none" and "other" as valid answers
                        if (!questionValid && answer?.value === "none") {
                            questionValid = true;
                        }
                        if (
                            !questionValid &&
                            answer?.value === "other" &&
                            answer?.otherText?.trim()
                        ) {
                            questionValid = true;
                        }
                        break;

                    case "checkbox":
                        questionValid =
                            Array.isArray(answer?.values) &&
                            answer.values.length > 0;
                        // For checkbox, "none" is also a valid answer
                        if (!questionValid && answer?.none) {
                            questionValid = true;
                        }
                        break;

                    case "rating":
                        questionValid =
                            answer > 0 ||
                            (typeof answer === "string" &&
                                answer !== "" &&
                                parseInt(answer, 10) > 0);
                        break;

                    case "matrix":
                        if (question.matrixType === "radio") {
                            // For radio matrix, check if each row has at least one selected option
                            const responses = answer?.responses || {};
                            questionValid =
                                question.rows &&
                                question.rows.every(
                                    (row) => !!responses[row.id]
                                );
                        } else if (question.matrixType === "checkbox") {
                            // For checkbox matrix, check if there's at least one checked box
                            const checkboxResponses =
                                answer?.checkboxResponses || {};
                            questionValid =
                                Object.keys(checkboxResponses).length > 0;
                        } else {
                            questionValid = !!answer;
                        }
                        break;

                    default:
                        questionValid = !!answer;
                }

                if (!questionValid) {
                    props.errors[question.id] = "Pertanyaan ini wajib dijawab.";
                    isValid = false;
                }
            }
        }
    }

    return isValid;
};

// Finish questionnaire button handler
const finishQuestionnaire = () => {
    // First validate the current page
    if (!validateCurrentPageQuestions()) {
        const firstErrorElement = document.querySelector(".error-highlight");
        if (firstErrorElement) {
            firstErrorElement.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }
        return;
    }

    // Then validate all questions in the section
    if (!validateAllQuestions()) {
        // Go to the page containing the first error
        const invalidQuestionIds = Object.keys(props.errors);
        if (invalidQuestionIds.length > 0) {
            const firstInvalidQuestion = props.currentSection.questions.find(
                (q) => q.id === invalidQuestionIds[0]
            );
            const questionIndex =
                props.currentSection.questions.indexOf(firstInvalidQuestion);

            if (questionIndex !== -1 && questionsPerPage.value) {
                const pageWithError =
                    Math.floor(questionIndex / questionsPerPage.value) + 1;
                currentPage.value = pageWithError;

                // After page change, scroll to the error
                setTimeout(() => {
                    const errorElement =
                        document.querySelector(".error-highlight");
                    if (errorElement) {
                        errorElement.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                        });
                    }
                }, 100);
            }
        }
        return;
    }

    // If all validation passes, emit finish event
    emit("finish");
};
</script>

<style scoped>
/* Animations for the section content */
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

.animate-fade-in-delayed {
    animation: fadeIn 0.5s ease-out 0.3s forwards;
    opacity: 0;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Progress bar animation */
.progress-bar-animated {
    background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.15) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.15) 75%,
        transparent 75%,
        transparent
    );
    background-size: 1rem 1rem;
    animation: progress-bar-animation 1s linear infinite;
}

@keyframes progress-bar-animation {
    0% {
        background-position: 1rem 0;
    }
    100% {
        background-position: 0 0;
    }
}

/* Questions transition group animations */
.question-enter-active,
.question-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.question-enter-from,
.question-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

/* Hover effect for navigation buttons */
.btn-prev:hover,
.btn-next:hover,
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Question input component transition */
.question-input-component {
    transition: all 0.2s ease-in-out;
}

/* Error highlight for invalid inputs */
.error-highlight input,
.error-highlight textarea,
.error-highlight select {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}

/* Animation for validation error message */
@keyframes headShake {
    0% {
        transform: translateX(0);
    }
    6.5% {
        transform: translateX(-6px) rotateY(-9deg);
    }
    18.5% {
        transform: translateX(5px) rotateY(7deg);
    }
    31.5% {
        transform: translateX(-3px) rotateY(-5deg);
    }
    43.5% {
        transform: translateX(2px) rotateY(3deg);
    }
    50% {
        transform: translateX(0);
    }
}

.animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
}

.animate__headShake {
    animation-name: headShake;
    animation-timing-function: ease-in-out;
}
</style>
