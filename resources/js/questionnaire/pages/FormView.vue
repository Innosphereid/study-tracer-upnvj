<template>
    <div class="questionnaire-form-page min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="logo flex-shrink-0">
                            <img
                                src="/logo.svg"
                                alt="Logo UPNVJ"
                                class="h-8 w-auto"
                            />
                        </div>
                        <div class="hidden md:block ml-4">
                            <div class="text-lg font-semibold text-gray-900">
                                TraceStudy UPNVJ
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <a
                            href="https://upnvj.ac.id"
                            target="_blank"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-0.5 mr-2 h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Website UPNVJ
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Form Content Area -->
        <main class="flex-1 mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
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

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Current Step: Welcome, Questions, or Thank You -->
                <div v-if="currentStep === 'welcome'" class="p-8">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ questionnaire.welcomeScreen.title }}
                    </h1>
                    <div
                        class="mt-4 text-gray-600"
                        v-html="questionnaire.welcomeScreen.description"
                    ></div>

                    <div class="mt-8">
                        <button
                            type="button"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="startQuestionnaire"
                        >
                            Mulai
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
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-else-if="currentStep === 'questions'" class="p-8">
                    <!-- Progress bar -->
                    <div v-if="questionnaire.showProgressBar" class="mb-8">
                        <div
                            class="flex items-center justify-between text-xs text-gray-500 mb-1"
                        >
                            <span>{{ Math.round(progress) }}% Selesai</span>
                            <span v-if="questionnaire.showPageNumbers">
                                Halaman {{ currentSectionIndex + 1 }} dari
                                {{ totalSections }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-indigo-600 h-2 rounded-full"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Current Section -->
                    <div v-if="currentSection" class="space-y-8">
                        <div>
                            <h2 class="text-xl font-medium text-gray-900">
                                {{ currentSection.title }}
                            </h2>
                            <p
                                v-if="currentSection.description"
                                class="mt-1 text-gray-600"
                            >
                                {{ currentSection.description }}
                            </p>
                        </div>

                        <!-- Questions -->
                        <div class="space-y-6">
                            <div
                                v-for="question in currentSection.questions"
                                :key="question.id"
                                class="p-4 border border-gray-200 rounded-md"
                            >
                                <!-- Render Question based on type -->
                                <component
                                    :is="
                                        getQuestionComponent(
                                            question.type,
                                            question
                                        )
                                    "
                                    :question="question"
                                    v-model="answers[question.id]"
                                    :error="errors[question.id]"
                                    @validate="
                                        handleValidation($event, question.id)
                                    "
                                ></component>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-8 flex justify-between items-center">
                        <button
                            v-if="currentSectionIndex > 0"
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="previousSection"
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

                        <button
                            v-if="currentSectionIndex < totalSections - 1"
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="nextSection"
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
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            @click="submitQuestionnaire"
                            :disabled="isSubmitting"
                        >
                            <span v-if="isSubmitting">
                                <svg
                                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
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
                                Mengirim...
                            </span>
                            <span v-else>
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
                            </span>
                        </button>
                    </div>
                </div>

                <div v-else-if="currentStep === 'thankYou'" class="p-8">
                    <div class="text-center">
                        <div
                            class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 text-green-600"
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
                        </div>
                        <h1 class="mt-3 text-2xl font-bold text-gray-900">
                            {{ questionnaire.thankYouScreen.title }}
                        </h1>
                        <div
                            class="mt-4 text-gray-600"
                            v-html="questionnaire.thankYouScreen.description"
                        ></div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div
                class="max-w-7xl mx-auto py-6 px-4 overflow-hidden sm:px-6 lg:px-8"
            >
                <p class="text-center text-sm text-gray-500">
                    &copy; {{ currentYear }} TraceStudy UPNVJ. Hak Cipta
                    Dilindungi.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import ShortText from "../components/questions/ShortText.vue";
import LongText from "../components/questions/LongText.vue";
import RadioQuestion from "../components/questions/RadioQuestion.vue";
import CheckboxQuestion from "../components/questions/CheckboxQuestion.vue";
import DropdownQuestion from "../components/questions/DropdownQuestion.vue";
import RatingQuestion from "../components/questions/RatingQuestion.vue";
import YesNoQuestion from "../components/questions/YesNoQuestion.vue";
import FileUploadQuestion from "../components/questions/FileUploadQuestion.vue";
import LikertQuestion from "../components/questions/LikertQuestion.vue";
import MatrixQuestion from "../components/questions/MatrixQuestion.vue";
import RankingQuestion from "../components/questions/RankingQuestion.vue";

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
});

// UI state
const isLoading = ref(false);
const isSubmitting = ref(false);
const currentStep = ref("welcome");
const currentSectionIndex = ref(0);
const answers = ref({});
const errors = ref({});
const validationState = ref({});
const currentYear = computed(() => new Date().getFullYear());

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

    // Simple calculation: current section index + 1 / total sections
    return ((currentSectionIndex.value + 1) / totalSections.value) * 100;
});

// Methods
const startQuestionnaire = () => {
    currentStep.value = "questions";
    currentSectionIndex.value = 0;
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

    // Trigger validation for each question in the section
    currentSection.value.questions.forEach((question) => {
        if (question.required) {
            const answer = answers.value[question.id];

            // Check if we have a validation result
            const validation = validationState.value[question.id];

            if (!validation || !validation.isValid) {
                // Either no validation result or failed validation
                errors.value[question.id] =
                    validation?.errorMessage || "Pertanyaan ini wajib dijawab.";
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

    try {
        // Prepare submission data
        const submissionData = {
            questionnaireId: props.questionnaire.id,
            answers: answers.value,
        };

        // Send to server
        const response = await fetch("/api/questionnaire/submit", {
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

        // Show thank you screen on success
        currentStep.value = "thankYou";

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (error) {
        console.error("Error submitting questionnaire:", error);
        alert("Terjadi kesalahan saat mengirim jawaban. Silakan coba lagi.");
    } finally {
        isSubmitting.value = false;
    }
};

// Helper to get the correct component for question type
const getQuestionComponent = (type, question = null) => {
    const componentMap = {
        "short-text": ShortText,
        "long-text": LongText,
        radio: RadioQuestion,
        checkbox: CheckboxQuestion,
        dropdown: DropdownQuestion,
        rating: RatingQuestion,
        "yes-no": YesNoQuestion,
        "file-upload": FileUploadQuestion,
        likert: LikertQuestion,
        matrix: MatrixQuestion,
        ranking: RankingQuestion,
    };

    // Special handling for radio questions that might actually be yes-no questions
    if (type === "radio" && question && question.settings) {
        try {
            // Parse settings if it's a string
            const settings =
                typeof question.settings === "string"
                    ? JSON.parse(question.settings)
                    : question.settings;

            // Check if this radio question was intended to be a yes-no question
            if (settings.type === "yes-no") {
                return YesNoQuestion;
            }
        } catch (error) {
            console.error("Error parsing question settings:", error);
        }
    }

    return componentMap[type] || ShortText;
};

// Initialize answers with default values
onMounted(() => {
    // Initialize answers object
    if (props.questionnaire.sections) {
        props.questionnaire.sections.forEach((section) => {
            if (section.questions) {
                section.questions.forEach((question) => {
                    // Set default empty values based on question type
                    switch (question.type) {
                        case "short-text":
                        case "long-text":
                        case "email":
                            answers.value[question.id] = "";
                            break;

                        case "radio":
                        case "dropdown":
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

                        case "rating":
                            answers.value[question.id] = 0;
                            break;

                        case "yes-no":
                            answers.value[question.id] = "";
                            break;

                        case "file-upload":
                            answers.value[question.id] = [];
                            break;

                        case "likert":
                            answers.value[question.id] = 0;
                            break;

                        case "matrix":
                            if (question.matrixType === "radio") {
                                answers.value[question.id] = { responses: {} };
                            } else {
                                answers.value[question.id] = {
                                    checkboxResponses: {},
                                };
                            }
                            break;

                        case "ranking":
                            answers.value[question.id] = [];
                            break;

                        default:
                            answers.value[question.id] = null;
                    }
                });
            }
        });
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
