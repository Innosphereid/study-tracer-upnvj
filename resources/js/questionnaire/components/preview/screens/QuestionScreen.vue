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

            <!-- Questions -->
            <div class="space-y-6 questions-container">
                <TransitionGroup name="question">
                    <div
                        v-for="(question, index) in currentSection.questions"
                        :key="question.id"
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300"
                    >
                        <!-- Question Number & Text -->
                        <div class="mb-4">
                            <div class="flex items-start">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-800 text-sm font-medium mr-3"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div>
                                    <h3
                                        class="text-lg font-medium text-gray-900 mb-1"
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
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Question Component -->
                        <div class="pl-9">
                            <component
                                :is="getQuestionComponent(question.type)"
                                :question="question"
                                v-model="answers[question.id]"
                                :error="errors[question.id]"
                                class="question-input-component"
                            ></component>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div
            class="mt-10 flex justify-between items-center navigation-container"
            :class="{ 'animate-fade-in-delayed': animateSection }"
        >
            <button
                v-if="hasPreviousSection()"
                type="button"
                class="btn-prev inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                @click="$emit('previous')"
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
                v-if="hasNextSection()"
                type="button"
                class="btn-next inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                @click="$emit('next')"
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
                @click="$emit('finish')"
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

defineEmits(["next", "previous", "finish"]);

// UI State
const animateSection = ref(false);

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
    // Map question types to component names
    const componentMap = {
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
    };

    // Get the component name based on the type
    const componentName = componentMap[type] || "ShortTextQuestion";

    // Try to find the component in the dynamically imported modules
    for (const path in questionComponents) {
        if (path.includes(componentName)) {
            return questionComponents[path].default;
        }
    }

    // Fallback to a simple div if component not found
    return {
        template: `<div class="p-4 bg-yellow-50 border border-yellow-100 rounded-md text-sm text-yellow-800">
      Component for type "${type}" is not available in preview mode.
    </div>`,
        props: ["question", "modelValue"],
        emits: ["update:modelValue"],
    };
};

// Animation effect when the component mounts
onMounted(() => {
    // Delay the animation start slightly
    setTimeout(() => {
        animateSection.value = true;
    }, 100);
});
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
</style>
