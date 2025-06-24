<template>
    <div class="preview-app">
        <!-- Loading overlay with animation -->
        <Transition name="fade">
            <div
                v-if="loading"
                class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center"
            >
                <div class="loading-spinner">
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
                    <span class="mt-3 text-sm font-medium text-indigo-700"
                        >Memuat Pratinjau...</span
                    >
                </div>
            </div>
        </Transition>

        <!-- Preview Content -->
        <div class="preview-content" :class="{ 'blur-sm': loading }">
            <!-- Current Step Content with Animation -->
            <Transition name="slide-fade" mode="out-in">
                <component
                    :is="currentStepComponent"
                    :key="currentStep"
                    :questionnaire="questionnaire"
                    :currentSection="currentSection"
                    :progress="progress"
                    :answers="answers"
                    :errors="errors"
                    @start="startQuestionnaire"
                    @next="nextSection"
                    @previous="previousSection"
                    @finish="finishQuestionnaire"
                    @restart="restartPreview"
                />
            </Transition>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import WelcomeScreen from "./screens/WelcomeScreen.vue";
import QuestionScreen from "./screens/QuestionScreen.vue";
import ThankYouScreen from "./screens/ThankYouScreen.vue";

const props = defineProps({
    questionnaire: {
        type: Object,
        required: true,
    },
});

// UI state
const loading = ref(true);
const currentStep = ref("welcome");
const currentSectionIndex = ref(0);
const answers = ref({});
const errors = ref({});

// Computed properties
const totalSections = computed(() => props.questionnaire.sections?.length || 0);

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

const currentStepComponent = computed(() => {
    switch (currentStep.value) {
        case "welcome":
            return WelcomeScreen;
        case "questions":
            return QuestionScreen;
        case "thankYou":
            return ThankYouScreen;
        default:
            return WelcomeScreen;
    }
});

// Methods
const startQuestionnaire = () => {
    // Add a subtle animation transition
    currentStep.value = "questions";
    currentSectionIndex.value = 0;
    scrollToTop();
};

const nextSection = () => {
    // Validate current section (simplified for preview)
    validateCurrentSection();

    if (currentSectionIndex.value < totalSections.value - 1) {
        currentSectionIndex.value++;
        scrollToTop();
    } else {
        finishQuestionnaire();
    }
};

const previousSection = () => {
    if (currentSectionIndex.value > 0) {
        currentSectionIndex.value--;
        scrollToTop();
    }
};

const validateCurrentSection = () => {
    // In preview mode, we just clear any errors and return true
    errors.value = {};
    return true;
};

const finishQuestionnaire = () => {
    // Show thank you screen with animation
    currentStep.value = "thankYou";
    scrollToTop();
};

const restartPreview = () => {
    // Reset state for new preview
    currentStep.value = "welcome";
    currentSectionIndex.value = 0;
    answers.value = {};
    errors.value = {};
    scrollToTop();
};

const scrollToTop = () => {
    // Smooth scroll to top
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Initialize on component mount
onMounted(() => {
    // Log the incoming questionnaire data
    console.log("Raw questionnaire data:", props.questionnaire);

    // Process the questionnaire data to ensure it's in the expected format
    processQuestionnaireData();

    // Simulate loading for visual effect
    setTimeout(() => {
        loading.value = false;

        // Pre-fill example answers
        initializeAnswers();
    }, 800);
});

// Process the questionnaire data to ensure it matches the expected structure
const processQuestionnaireData = () => {
    console.log("Processing questionnaire data");

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

    // Map backend question types to frontend types
    const questionTypeMap = {
        text: "short-text",
        textarea: "long-text",
        radio: "radio",
        checkbox: "checkbox",
        dropdown: "dropdown",
        rating: "rating",
        date: "date",
        file: "file-upload",
        matrix: "matrix",
        ranking: "ranking",
    };

    // If the questionnaire has sections, ensure each section's questions have the expected structure
    if (props.questionnaire.sections) {
        console.log(
            `Processing ${props.questionnaire.sections.length} sections`
        );

        props.questionnaire.sections.forEach((section, sectionIndex) => {
            console.log(
                `Processing section ${sectionIndex + 1}: ${section.title}`
            );

            // Ensure the section has a valid ID
            if (!section.id) {
                section.id = `section-${sectionIndex}`;
            }

            // If the section has questions, process each question
            if (section.questions && section.questions.length > 0) {
                console.log(
                    `Processing ${
                        section.questions.length
                    } questions in section ${sectionIndex + 1}`
                );

                section.questions.forEach((question, questionIndex) => {
                    // Map backend question_type to frontend type
                    if (
                        question.question_type &&
                        questionTypeMap[question.question_type]
                    ) {
                        question.type = questionTypeMap[question.question_type];
                        console.log(
                            `Mapped question type from ${question.question_type} to ${question.type}`
                        );
                    } else if (!question.type) {
                        // If no type mapping exists, default to short-text
                        question.type = "short-text";
                        console.log(
                            `No type found for question, defaulting to short-text`
                        );
                    }

                    // Ensure the question has an ID
                    if (!question.id) {
                        question.id = `question-${sectionIndex}-${questionIndex}`;
                    }

                    // Map title to text if needed
                    if (question.title && !question.text) {
                        question.text = question.title;
                    }

                    // Map description to helpText if needed
                    if (question.description && !question.helpText) {
                        question.helpText = question.description;
                    }

                    // Map is_required to required if needed
                    if (
                        question.is_required !== undefined &&
                        question.required === undefined
                    ) {
                        question.required = question.is_required;
                    }

                    // Process options if they exist
                    if (question.options && question.options.length > 0) {
                        question.options.forEach((option, optionIndex) => {
                            // Map label to text if needed
                            if (option.label && !option.text) {
                                option.text = option.label;
                            }

                            // Ensure the option has an ID
                            if (!option.id) {
                                option.id = `option-${sectionIndex}-${questionIndex}-${optionIndex}`;
                            }
                        });
                    }
                });
            } else {
                console.warn(
                    `No questions found in section ${sectionIndex + 1}`
                );
            }
        });
    } else {
        console.warn("No sections found in questionnaire");
    }

    // Log the processed questionnaire data
    console.log("Processed questionnaire data:", props.questionnaire);
};

const initializeAnswers = () => {
    if (props.questionnaire.sections) {
        props.questionnaire.sections.forEach((section) => {
            if (section.questions) {
                section.questions.forEach((question) => {
                    // Set default or sample values based on question type
                    switch (question.type) {
                        case "short-text":
                        case "email":
                            answers.value[question.id] = "";
                            break;
                        case "long-text":
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
                        default:
                            answers.value[question.id] = null;
                    }
                });
            }
        });
    }
};
</script>

<style scoped>
/* Elegant animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.2s ease-in;
}
.slide-fade-enter-from {
    transform: translateY(20px);
    opacity: 0;
}
.slide-fade-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}

/* Loading spinner pulse effect */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
.loading-spinner span {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
