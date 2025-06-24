<template>
    <div class="question-component">
        <fieldset>
            <legend class="text-sm font-medium text-gray-700">
                {{ question.text }}
                <span v-if="question.required" class="text-red-500">*</span>
            </legend>

            <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
                {{ question.helpText }}
            </p>

            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <div
                        v-if="question.labels && question.labels[minRating]"
                        class="text-sm text-gray-500"
                    >
                        {{ question.labels[minRating] }}
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        {{ minRating }}
                    </div>

                    <div
                        v-if="
                            question.labels && question.labels[maxRatingValue]
                        "
                        class="text-sm text-gray-500"
                    >
                        {{ question.labels[maxRatingValue] }}
                    </div>
                    <div v-else class="text-sm text-gray-500">
                        {{ maxRatingValue }}
                    </div>
                </div>

                <div class="rating-stars mt-2 flex items-center justify-center">
                    <div
                        v-for="n in maxRating"
                        :key="n"
                        class="rating-star p-1 cursor-pointer"
                        @click="selectRating(calculateRatingValue(n))"
                        @mouseenter="showHover(calculateRatingValue(n))"
                        @mouseleave="clearHover()"
                        :class="{ 'opacity-50': isBuilder }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            :class="[
                                'w-8 h-8 transition-all',
                                isRatingActive(n)
                                    ? 'text-yellow-400'
                                    : 'text-gray-300',
                            ]"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </div>

                <div class="text-center mt-2">
                    <span class="text-sm font-medium" v-if="internalValue">
                        {{ internalValue }} / {{ maxRatingValue }}
                    </span>
                    <span
                        v-else-if="hoveredRating"
                        class="text-sm text-gray-500"
                    >
                        {{ hoveredRating }} / {{ maxRatingValue }}
                    </span>
                    <span v-else class="text-sm text-gray-400">
                        Klik untuk memberi rating
                    </span>
                </div>
            </div>
        </fieldset>

        <div v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Computed properties for rating settings
const minRating = computed(() => props.question.minRating || 1);
const maxRating = computed(() => Math.min(props.question.maxRating || 5, 10)); // Ensure max 10 stars
const maxRatingValue = computed(
    () => props.question.maxRatingValue || maxRating.value
);
const stepValue = computed(() => props.question.stepValue || 1);

// Internal state
const internalValue = ref(props.modelValue || 0);
const hoveredRating = ref(0);

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        internalValue.value = newVal || 0;
    }
);

// Calculate rating value for a star position
const calculateRatingValue = (position) => {
    // Map position to value based on min/max and step
    const range = maxRatingValue.value - minRating.value;
    const stepsPerStar = range / (maxRating.value - 1);

    return (
        Math.round(
            (minRating.value + (position - 1) * stepsPerStar) / stepValue.value
        ) * stepValue.value
    );
};

// Check if a star position should be active
const isRatingActive = (position) => {
    const ratingForPosition = calculateRatingValue(position);

    if (hoveredRating.value) {
        return hoveredRating.value >= ratingForPosition;
    }

    return internalValue.value >= ratingForPosition;
};

const selectRating = (rating) => {
    if (props.isBuilder) return;

    internalValue.value = rating;
    emit("update:modelValue", rating);
    validate();
};

const showHover = (rating) => {
    if (props.isBuilder) return;
    hoveredRating.value = rating;
};

const clearHover = () => {
    hoveredRating.value = 0;
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !internalValue.value) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
.rating-star:hover {
    transform: scale(1.1);
}

.rating-star svg {
    width: 2.5rem;
    height: 2.5rem;
}
</style>
