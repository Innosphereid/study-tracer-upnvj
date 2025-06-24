<template>
    <div class="slider-question">
        <div class="relative">
            <div class="flex justify-between mb-2">
                <span class="text-xs text-gray-500">{{
                    question.min || 0
                }}</span>
                <span class="text-xs text-gray-500">{{
                    question.max || 100
                }}</span>
            </div>

            <div class="mb-6">
                <input
                    type="range"
                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-500"
                    :min="question.min || 0"
                    :max="question.max || 100"
                    :step="question.step || 1"
                    :value="modelValue"
                    @input="updateValue($event.target.value)"
                />

                <div class="mt-2 flex justify-center">
                    <span
                        class="inline-flex items-center justify-center h-8 w-12 rounded-full bg-indigo-500 text-white text-sm font-medium"
                        :style="{ transform: 'translateX(0px)' }"
                    >
                        {{ modelValue || 0 }}
                    </span>
                </div>
            </div>

            <!-- Labels below slider if provided -->
            <div
                v-if="
                    question.labels && Object.keys(question.labels).length > 0
                "
                class="flex justify-between text-xs text-gray-600"
            >
                <template
                    v-for="(label, value) in question.labels"
                    :key="value"
                >
                    <span>{{ label }}</span>
                </template>
            </div>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Helper to update value
const updateValue = (value) => {
    // Ensure value is within bounds
    const min = props.question.min || 0;
    const max = props.question.max || 100;

    // Convert to number
    let numericValue = Number(value);

    // Clamp to min/max range
    numericValue = Math.max(min, Math.min(max, numericValue));

    emit("update:modelValue", numericValue.toString());
};

// When component mounts, ensure we have a default value
const initializeDefaultValue = () => {
    if (!props.modelValue && props.modelValue !== 0) {
        const defaultValue =
            props.question.defaultValue ||
            ((props.question.min || 0) + (props.question.max || 100)) / 2;
        updateValue(defaultValue);
    }
};

// Call initialization
initializeDefaultValue();
</script>

<style scoped>
.slider-question input[type="range"] {
    -webkit-appearance: none;
    height: 8px;
    background: #e5e7eb;
    border-radius: 5px;
    background-image: linear-gradient(#4f46e5, #4f46e5);
    background-repeat: no-repeat;
}

/* Track styling (Firefox) */
.slider-question input[type="range"]::-moz-range-track {
    background: #e5e7eb;
    border-radius: 5px;
    height: 8px;
}

/* Thumb styling */
.slider-question input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    transition: all 0.3s ease-in-out;
    margin-top: -6px;
}

.slider-question input[type="range"]::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    border: none;
    transition: all 0.3s ease-in-out;
}

/* Thumb hover state */
.slider-question input[type="range"]::-webkit-slider-thumb:hover {
    box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.4);
}

.slider-question input[type="range"]::-moz-range-thumb:hover {
    box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.4);
}

/* Active/focus state */
.slider-question input[type="range"]:active::-webkit-slider-thumb,
.slider-question input[type="range"]:focus::-webkit-slider-thumb {
    box-shadow: 0 0 0 7px rgba(79, 70, 229, 0.3);
    transform: scale(1.2);
}

.slider-question input[type="range"]:active::-moz-range-thumb,
.slider-question input[type="range"]:focus::-moz-range-thumb {
    box-shadow: 0 0 0 7px rgba(79, 70, 229, 0.3);
    transform: scale(1.2);
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
