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
    // Simulate loading for visual effect
    setTimeout(() => {
        loading.value = false;

        // Pre-fill example answers
        initializeAnswers();
    }, 800);
});

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
