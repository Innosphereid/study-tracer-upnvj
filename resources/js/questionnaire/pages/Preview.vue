<template>
    <div
        class="questionnaire-preview-page min-h-screen flex flex-col bg-gray-50"
    >
        <!-- Preview Header with Actions -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            Mode Pratinjau
                        </span>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                            Kembali ke Editor
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click="publishQuestionnaire"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="-ml-1 mr-2 h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Terbitkan
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Preview -->
        <main class="flex-1 mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Current Step: Welcome, Questions, or Thank You -->
                <div v-if="currentStep === 'welcome'" class="p-8">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ questionnaire.welcomeScreen.title }}
                    </h1>
                    <div class="mt-4 text-gray-600">
                        {{ questionnaire.welcomeScreen.description }}
                    </div>

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
                        <div class="mt-4 text-gray-600">
                            {{ questionnaire.thankYouScreen.description }}
                        </div>

                        <div class="mt-8">
                            <button
                                type="button"
                                class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                @click="restartPreview"
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
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    />
                                </svg>
                                Lihat Pratinjau Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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

// Navigation state
const currentStep = ref("welcome");
const currentSectionIndex = ref(0);
const answers = ref({});
const errors = ref({});

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
    }
};

const validateCurrentSection = () => {
    let isValid = true;
    errors.value = {};

    if (!currentSection.value) return true;

    // Check each question in the section
    for (const question of currentSection.value.questions) {
        if (question.required) {
            // Different validation based on question type
            let questionValid = false;

            switch (question.type) {
                case "short-text":
                case "long-text":
                case "email":
                    questionValid = !!answers.value[question.id]?.trim();
                    break;

                case "radio":
                case "dropdown":
                    questionValid = !!answers.value[question.id]?.value;
                    break;

                case "checkbox":
                    questionValid =
                        Array.isArray(answers.value[question.id]?.values) &&
                        answers.value[question.id]?.values.length > 0;
                    break;

                case "rating":
                    questionValid = !!answers.value[question.id];
                    break;

                case "matrix":
                    if (question.matrixType === "radio") {
                        // For radio matrix, check if each row has at least one selected option
                        const responses =
                            answers.value[question.id]?.responses || {};
                        questionValid =
                            question.rows &&
                            question.rows.every((row) => !!responses[row.id]);
                    } else if (question.matrixType === "checkbox") {
                        // For checkbox matrix, check if there's at least one checked box
                        const checkboxResponses =
                            answers.value[question.id]?.checkboxResponses || {};
                        questionValid =
                            Object.keys(checkboxResponses).length > 0;
                    } else {
                        questionValid = !!answers.value[question.id];
                    }
                    break;

                default:
                    // Default validation just checks if there's any value
                    questionValid = !!answers.value[question.id];
            }

            if (!questionValid) {
                console.log(
                    `Question ${question.id} (${question.text}) failed validation. Type: ${question.type}, Answer:`,
                    answers.value[question.id]
                );
                errors.value[question.id] = "Pertanyaan ini wajib dijawab.";
                isValid = false;
            }
        }
    }

    if (!isValid) {
        console.log("Validation failed with errors:", errors.value);
    }

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

const finishQuestionnaire = () => {
    // Validate final section before finishing
    if (!validateCurrentSection()) {
        return;
    }

    // In a real form, we would submit the answers here
    const processedAnswers = processAnswersBeforeSubmit(answers.value);
    console.log("Answers:", processedAnswers);

    // Show thank you screen
    currentStep.value = "thankYou";

    // Scroll to top
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Fungsi untuk memproses jawaban sebelum dikirim ke server
const processAnswersBeforeSubmit = (answersData) => {
    const processedAnswers = { ...answersData };

    // Cari semua pertanyaan dalam seluruh section
    const allQuestions = [];
    if (props.questionnaire.sections) {
        props.questionnaire.sections.forEach((section) => {
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
    });

    return processedAnswers;
};

const restartPreview = () => {
    // Reset state for new preview
    currentStep.value = "welcome";
    currentSectionIndex.value = 0;
    answers.value = {};
    errors.value = {};
};

const goBack = () => {
    // Go back to editor
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback if no history
        window.location.href = "/kuesioner";
    }
};

const publishQuestionnaire = () => {
    // Redirect to editor with publish modal open
    const questionnaireId = props.questionnaire.id || "draft";
    window.location.href = `/kuesioner/${questionnaireId}/edit?publish=true`;
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
    // Parse settings if it's a string
    if (
        props.questionnaire.settings &&
        typeof props.questionnaire.settings === "string"
    ) {
        try {
            const settingsObj = JSON.parse(props.questionnaire.settings);
            console.log("Parsed settings:", settingsObj);

            // Create welcome screen and thank you screen objects if they don't exist
            if (!props.questionnaire.welcomeScreen) {
                props.questionnaire.welcomeScreen = {};
            }

            if (!props.questionnaire.thankYouScreen) {
                props.questionnaire.thankYouScreen = {};
            }

            // Apply parsed settings to the questionnaire object
            props.questionnaire.showProgressBar = settingsObj.showProgressBar;
            props.questionnaire.showPageNumbers = settingsObj.showPageNumbers;
            props.questionnaire.requiresLogin = settingsObj.requiresLogin;

            // Set welcome screen data - directly assign properties for reactivity
            if (settingsObj.welcomeScreen) {
                props.questionnaire.welcomeScreen.title =
                    settingsObj.welcomeScreen.title;
                props.questionnaire.welcomeScreen.description =
                    settingsObj.welcomeScreen.description;
                console.log(
                    "Set welcome screen title:",
                    props.questionnaire.welcomeScreen.title
                );
                console.log(
                    "Set welcome screen description:",
                    props.questionnaire.welcomeScreen.description
                );
            }

            // Set thank you screen data - directly assign properties for reactivity
            if (settingsObj.thankYouScreen) {
                props.questionnaire.thankYouScreen.title =
                    settingsObj.thankYouScreen.title;
                props.questionnaire.thankYouScreen.description =
                    settingsObj.thankYouScreen.description;
                console.log(
                    "Set thank you screen:",
                    props.questionnaire.thankYouScreen
                );
            }
        } catch (error) {
            console.error("Error parsing questionnaire settings:", error);
        }
    }

    // Log questionnaire data after processing settings
    console.log(
        "Welcome screen after processing:",
        JSON.stringify(props.questionnaire.welcomeScreen, null, 2)
    );

    // For preview purposes, pre-fill some responses
    if (props.questionnaire.sections) {
        props.questionnaire.sections.forEach((section) => {
            if (section.questions) {
                section.questions.forEach((question) => {
                    // Set default values based on question type
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
                            answers.value[question.id] = { responses: {} };
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
/* Additional component-specific styles can be added here */
</style>
