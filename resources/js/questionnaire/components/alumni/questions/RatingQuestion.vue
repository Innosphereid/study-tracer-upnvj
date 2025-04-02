<template>
    <div class="rating-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <div class="flex items-center justify-center py-2">
                    <div class="rating-stars flex">
                        <button
                            v-for="i in maxRating"
                            :key="i"
                            type="button"
                            @click="updateRating(i)"
                            @mouseenter="hoverRating = i"
                            @mouseleave="hoverRating = 0"
                            class="star-button p-1 focus:outline-none transition-transform duration-200 transform hover:scale-110"
                            :class="{
                                'scale-110': (hoverRating || modelValue) === i,
                            }"
                            aria-label="Rate this as {{ i }} out of {{ maxRating }}"
                        >
                            <svg
                                class="w-8 h-8 sm:w-10 sm:h-10"
                                :class="{
                                    'text-yellow-400 fill-current':
                                        i <= (hoverRating || modelValue),
                                    'text-gray-300':
                                        i > (hoverRating || modelValue),
                                }"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    class="rating-labels flex justify-between text-sm text-gray-600 mt-2 px-1"
                >
                    <span>{{ minLabel }}</span>
                    <span>{{ maxLabel }}</span>
                </div>
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
import { defineProps, defineEmits, ref, computed, watch } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Number,
        default: 0,
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Track hover state
const hoverRating = ref(0);

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

// Get max rating
const maxRating = computed(() => {
    return settings.value.maxRating || 5;
});

// Get min label
const minLabel = computed(() => {
    return settings.value.minLabel || "Sangat Buruk";
});

// Get max label
const maxLabel = computed(() => {
    return settings.value.maxLabel || "Sangat Baik";
});

// Update the rating
const updateRating = (rating) => {
    // If clicking the same rating, clear it
    const newRating = props.modelValue === rating ? 0 : rating;
    emit("update:modelValue", newRating);

    // Validasi segera setelah nilai berubah
    setTimeout(() => validate(), 0);
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (
        props.question.required &&
        (props.modelValue === null || props.modelValue === 0)
    ) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};

// Watch for changes in modelValue to trigger validation
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue && newValue > 0) {
            validate();
        }
    },
    { immediate: true }
);
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
