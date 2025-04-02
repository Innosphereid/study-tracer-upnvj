<template>
    <div class="alumni-questionnaire-app min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <questionnaire-header
            :title="questionnaire.title"
            :subtitle="'TraceStudy Alumni UPNVJ'"
        />

        <!-- Main Content -->
        <main class="flex-1 mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="isLoading" class="flex justify-center items-center h-64">
                <div class="spinner">
                    <svg
                        class="animate-spin h-10 w-10 text-indigo-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                </div>
            </div>

            <div v-else>
                <!-- Welcome Screen -->
                <welcome-screen
                    v-if="currentStep === 'welcome'"
                    :title="welcomeScreenTitle"
                    :subtitle="welcomeScreenSubtitle"
                    :description="welcomeScreenDescription"
                    @start="startQuestionnaire"
                />

                <!-- Questionnaire Content -->
                <div v-else-if="currentStep === 'questions'" class="space-y-8">
                    <!-- Progress Bar -->
                    <progress-bar
                        v-if="questionnaire.showProgressBar"
                        :progress="progress"
                        :current-section="currentSectionIndex + 1"
                        :total-sections="totalSections"
                        :show-section-numbers="questionnaire.showPageNumbers"
                    />

                    <!-- Current Section -->
                    <section-container
                        v-if="currentSection"
                        :title="currentSection.title"
                        :description="currentSection.description"
                    >
                        <!-- Questions -->
                        <template
                            v-for="(
                                question, index
                            ) in currentSection.questions"
                            :key="question.id"
                        >
                            <!-- Use dynamic component to load the correct question type -->
                            <component
                                :is="getQuestionComponent(question.type)"
                                :question="question"
                                v-model="answers[question.id]"
                                :error="errors[question.id]"
                                @validate="
                                    handleValidation($event, question.id)
                                "
                            />
                        </template>
                    </section-container>

                    <!-- Navigation -->
                    <questionnaire-navigation
                        :show-previous="currentSectionIndex > 0"
                        :show-next="currentSectionIndex < totalSections - 1"
                        :show-submit="currentSectionIndex === totalSections - 1"
                        :is-submitting="isSubmitting"
                        @previous="previousSection"
                        @next="nextSection"
                        @submit="submitQuestionnaire"
                    />
                </div>

                <!-- Thank You Screen -->
                <thank-you-screen
                    v-else-if="currentStep === 'thankYou'"
                    :title="thankYouScreenTitle"
                    :description="thankYouScreenDescription"
                    :show-home-button="true"
                    :home-url="'/'"
                >
                </thank-you-screen>
            </div>
        </main>

        <!-- Footer -->
        <questionnaire-footer />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import QuestionnaireHeader from "./ui/QuestionnaireHeader.vue";
import QuestionnaireFooter from "./ui/QuestionnaireFooter.vue";
import WelcomeScreen from "./screens/WelcomeScreen.vue";
import ThankYouScreen from "./screens/ThankYouScreen.vue";
import SectionContainer from "./ui/SectionContainer.vue";
import ProgressBar from "./ui/ProgressBar.vue";
import QuestionnaireNavigation from "./ui/QuestionnaireNavigation.vue";

// Question components
import ShortTextQuestion from "./questions/ShortTextQuestion.vue";
import LongTextQuestion from "./questions/LongTextQuestion.vue";
import RadioQuestion from "./questions/RadioQuestion.vue";
import CheckboxQuestion from "./questions/CheckboxQuestion.vue";
import DateQuestion from "./questions/DateQuestion.vue";
import DropdownQuestion from "./questions/DropdownQuestion.vue";
import EmailQuestion from "./questions/EmailQuestion.vue";
import FileUploadQuestion from "./questions/FileUploadQuestion.vue";
import LikertQuestion from "./questions/LikertQuestion.vue";
import MatrixQuestion from "./questions/MatrixQuestion.vue";
import NumberQuestion from "./questions/NumberQuestion.vue";
import PhoneQuestion from "./questions/PhoneQuestion.vue";
import RankingQuestion from "./questions/RankingQuestion.vue";
import RatingQuestion from "./questions/RatingQuestion.vue";
import SliderQuestion from "./questions/SliderQuestion.vue";
import YesNoQuestion from "./questions/YesNoQuestion.vue";

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
    isPreview: {
        type: Boolean,
        default: false,
    },
});

// Debug flag - automatically enable in development mode
const debug = ref(
    process.env.NODE_ENV === "development" ||
        window.location.href.includes("debug=true")
);

// Add some debug logs
if (debug.value) {
    console.log("AlumniQuestionnaireApp initialized with props:", props);
    console.log("Questionnaire data:", props.questionnaire);
    console.log("Sections:", props.questionnaire.sections);

    if (
        props.questionnaire.sections &&
        props.questionnaire.sections.length > 0
    ) {
        console.log(
            "First section questions:",
            props.questionnaire.sections[0].questions
        );
    }
}

// UI state
const isLoading = ref(false);
const isSubmitting = ref(false);
const currentStep = ref("welcome");
const currentSectionIndex = ref(0);
const answers = ref({});
const errors = ref({});
const validationState = ref({});

// Computed properties
const totalSections = computed(() => props.questionnaire.sections.length);

const currentSection = computed(() => {
    if (
        currentSectionIndex.value >= 0 &&
        currentSectionIndex.value < totalSections.value
    ) {
        return props.questionnaire.sections[currentSectionIndex.value];
    }
    return null;
});

const progress = computed(() => {
    if (totalSections.value === 0) return 0;
    return ((currentSectionIndex.value + 1) / totalSections.value) * 100;
});

// Helper to get the correct component for question type
const getQuestionComponent = (type) => {
    console.log("Getting component for type:", type);

    // Normalize type dari database
    // Beberapa data mungkin menggunakan question_type atau type
    let normalizedType = type ? type.toLowerCase() : "short-text";

    // Handle case when type is different from what's expected
    // Convert from database keys to component keys
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

    // If the type is in our mapping, use the normalized version
    if (typeMapping[normalizedType]) {
        normalizedType = typeMapping[normalizedType];
        console.log(
            `Normalized question type from ${type} to ${normalizedType}`
        );
    }

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

    if (!componentMap[normalizedType]) {
        console.warn(
            `Unknown question type: ${type} (normalized to ${normalizedType}), falling back to short-text`
        );
    }

    return componentMap[normalizedType] || ShortTextQuestion; // Fallback to short text if type not found
};

// Welcome Screen computed properties with fallbacks
const welcomeScreenTitle = computed(() => {
    // Check various possible locations for the welcome screen title
    if (
        props.questionnaire.welcomeScreen &&
        props.questionnaire.welcomeScreen.title
    ) {
        return props.questionnaire.welcomeScreen.title;
    }

    if (
        props.questionnaire.settings &&
        props.questionnaire.settings.welcomeScreen &&
        props.questionnaire.settings.welcomeScreen.title
    ) {
        return props.questionnaire.settings.welcomeScreen.title;
    }

    return props.questionnaire.title || "Selamat Datang di Kuesioner Alumni";
});

const welcomeScreenSubtitle = computed(() => {
    if (
        props.questionnaire.welcomeScreen &&
        props.questionnaire.welcomeScreen.subtitle
    ) {
        return props.questionnaire.welcomeScreen.subtitle;
    }

    if (
        props.questionnaire.settings &&
        props.questionnaire.settings.welcomeScreen &&
        props.questionnaire.settings.welcomeScreen.subtitle
    ) {
        return props.questionnaire.settings.welcomeScreen.subtitle;
    }

    return "TraceStudy UPNVJ";
});

const welcomeScreenDescription = computed(() => {
    if (
        props.questionnaire.welcomeScreen &&
        props.questionnaire.welcomeScreen.description
    ) {
        return props.questionnaire.welcomeScreen.description;
    }

    if (
        props.questionnaire.settings &&
        props.questionnaire.settings.welcomeScreen &&
        props.questionnaire.settings.welcomeScreen.description
    ) {
        return props.questionnaire.settings.welcomeScreen.description;
    }

    return "Terima kasih telah berpartisipasi dalam kuesioner ini. Jawaban Anda sangat berarti bagi kami.";
});

// Thank You Screen computed properties with fallbacks
const thankYouScreenTitle = computed(() => {
    if (
        props.questionnaire.thankYouScreen &&
        props.questionnaire.thankYouScreen.title
    ) {
        return props.questionnaire.thankYouScreen.title;
    }

    if (
        props.questionnaire.settings &&
        props.questionnaire.settings.thankYouScreen &&
        props.questionnaire.settings.thankYouScreen.title
    ) {
        return props.questionnaire.settings.thankYouScreen.title;
    }

    return "Terima Kasih!";
});

const thankYouScreenDescription = computed(() => {
    if (
        props.questionnaire.thankYouScreen &&
        props.questionnaire.thankYouScreen.description
    ) {
        return props.questionnaire.thankYouScreen.description;
    }

    if (
        props.questionnaire.settings &&
        props.questionnaire.settings.thankYouScreen &&
        props.questionnaire.settings.thankYouScreen.description
    ) {
        return props.questionnaire.settings.thankYouScreen.description;
    }

    return "Jawaban Anda telah berhasil disimpan. Terima kasih atas partisipasi Anda dalam kuesioner ini.";
});

// Methods
const startQuestionnaire = () => {
    currentStep.value = "questions";
    currentSectionIndex.value = 0;

    // Scroll to top
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const previousSection = () => {
    if (currentSectionIndex.value > 0) {
        currentSectionIndex.value--;

        // Scroll to top of new section
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

const handleValidation = (validationResult, questionId) => {
    validationState.value[questionId] = validationResult;

    if (!validationResult.isValid) {
        errors.value[questionId] = validationResult.errorMessage;
    } else {
        delete errors.value[questionId];
    }
};

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

const submitQuestionnaire = async () => {
    // Validate final section before submitting
    if (!validateCurrentSection()) {
        return;
    }

    isSubmitting.value = true;

    if (props.isPreview) {
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
            slug: props.questionnaire.slug || props.questionnaire.id,
            answers: answers.value,
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
        alert("Terjadi kesalahan saat mengirim jawaban. Silakan coba lagi.");
    } finally {
        isSubmitting.value = false;
    }
};

// Initialize answers with default values
onMounted(() => {
    console.log(
        "AlumniQuestionnaireApp mounted with questionnaire:",
        props.questionnaire
    );

    // Transform question data if needed
    if (props.questionnaire.sections) {
        props.questionnaire.sections.forEach((section, sectionIndex) => {
            console.log(
                `Processing section ${sectionIndex + 1}:`,
                section.title
            );

            if (section.questions) {
                section.questions.forEach((question, questionIndex) => {
                    // Debug
                    console.log(`Question ${questionIndex + 1}:`, question);

                    // Ensure question has type field (might be in settings or question_type)
                    if (!question.type) {
                        // Check if it's in question_type field (from database model)
                        if (question.question_type) {
                            question.type = question.question_type;
                            console.log(
                                `Set question type from question_type: ${question.type}`
                            );
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
                                    console.log(
                                        `Set question type from settings: ${question.type}`
                                    );
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
                            question.settings = JSON.parse(question.settings);
                            console.log(
                                `Parsed settings for question ${
                                    questionIndex + 1
                                }`
                            );
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
                        console.log(
                            `Set question text from title: ${question.text}`
                        );
                    }

                    // Ensure helpText field exists (may be stored in description)
                    if (!question.helpText && question.description) {
                        question.helpText = question.description;
                        console.log(
                            `Set question helpText from description: ${question.helpText}`
                        );
                    }

                    // Ensure required field exists (may be stored in is_required)
                    if (
                        question.required === undefined &&
                        question.is_required !== undefined
                    ) {
                        question.required = question.is_required;
                        console.log(
                            `Set question required from is_required: ${question.required}`
                        );
                    }

                    // Initialize answers object based on question types
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
                            if (
                                question.settings &&
                                question.settings.statements
                            ) {
                                const likertValues = {};
                                question.settings.statements.forEach(
                                    (statement) => {
                                        likertValues[statement.id] = null;
                                    }
                                );
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
                                answers.value[question.id] =
                                    question.options.map((option) => ({
                                        id: option.id || option.value,
                                        label: option.label,
                                        value: option.value,
                                        order: option.order,
                                    }));
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
});
</script>

<style scoped>
.spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
