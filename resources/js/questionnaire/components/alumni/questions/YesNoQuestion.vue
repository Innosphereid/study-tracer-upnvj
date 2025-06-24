<template>
    <div class="yes-no-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div
                class="mt-3 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3"
            >
                <button
                    type="button"
                    @click="updateValue('yes')"
                    class="flex-1 px-4 py-3 rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :class="{
                        'bg-green-50 border-green-500 text-green-700':
                            modelValue === 'yes',
                        'bg-white border-gray-300 text-gray-700 hover:bg-gray-50':
                            modelValue !== 'yes',
                    }"
                >
                    <div class="flex items-center justify-center">
                        <svg
                            v-if="modelValue === 'yes'"
                            class="w-5 h-5 mr-2 text-green-500"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="font-medium">{{ yesLabel }}</span>
                    </div>
                </button>

                <button
                    type="button"
                    @click="updateValue('no')"
                    class="flex-1 px-4 py-3 rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :class="{
                        'bg-red-50 border-red-500 text-red-700':
                            modelValue === 'no',
                        'bg-white border-gray-300 text-gray-700 hover:bg-gray-50':
                            modelValue !== 'no',
                    }"
                >
                    <div class="flex items-center justify-center">
                        <svg
                            v-if="modelValue === 'no'"
                            class="w-5 h-5 mr-2 text-red-500"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="font-medium">{{ noLabel }}</span>
                    </div>
                </button>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-3 text-sm text-red-600">
                    {{ error }}
                </p>
            </transition>
        </question-container>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Parse settings
const settings = computed(() => {
    if (!props.question.settings) return {};

    if (typeof props.question.settings === "string") {
        try {
            return JSON.parse(props.question.settings);
        } catch (e) {
            console.error("Failed to parse settings:", e);
            return {};
        }
    }

    return props.question.settings;
});

// Get yes label
const yesLabel = computed(() => {
    return settings.value.yesLabel || "Ya";
});

// Get no label
const noLabel = computed(() => {
    return settings.value.noLabel || "Tidak";
});

// Update the value
const updateValue = (value) => {
    // If clicking the same value, clear it
    const newValue = props.modelValue === value ? "" : value;
    emit("update:modelValue", newValue);
    validate();
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !props.modelValue) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
