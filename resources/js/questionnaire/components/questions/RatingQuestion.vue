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
                        v-if="question.labels && question.labels[1]"
                        class="text-sm text-gray-500"
                    >
                        {{ question.labels[1] }}
                    </div>

                    <div
                        v-if="
                            question.labels &&
                            question.labels[question.maxRating]
                        "
                        class="text-sm text-gray-500"
                    >
                        {{ question.labels[question.maxRating] }}
                    </div>
                </div>

                <div class="rating-stars mt-2 flex items-center justify-center">
                    <div
                        v-for="n in question.maxRating"
                        :key="n"
                        class="rating-star p-1 cursor-pointer"
                        @click="selectRating(n)"
                        @mouseenter="showHover(n)"
                        @mouseleave="clearHover()"
                        :class="{ 'opacity-50': isBuilder }"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            :class="[
                                'w-8 h-8 transition-all',
                                hoveredRating >= n || internalValue >= n
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
                        {{ internalValue }} / {{ question.maxRating }}
                    </span>
                    <span
                        v-else-if="hoveredRating"
                        class="text-sm text-gray-500"
                    >
                        {{ hoveredRating }} / {{ question.maxRating }}
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
import { ref, defineProps, defineEmits, watch } from "vue";

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
</style>
