<template>
    <div class="slider-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <div class="slider-container py-4">
                    <div class="flex items-center justify-center mb-2">
                        <span class="font-medium text-xl text-indigo-600">{{
                            displayValue
                        }}</span>
                    </div>

                    <div class="relative">
                        <input
                            type="range"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            :min="minValue"
                            :max="maxValue"
                            :step="step"
                            :value="modelValue"
                            @input="updateValue($event.target.value)"
                            @change="validate"
                        />

                        <div
                            class="slider-labels flex justify-between text-xs text-gray-600 px-1 mt-2"
                        >
                            <span>{{ minLabel }}</span>
                            <span v-if="showMidLabel">{{ midLabel }}</span>
                            <span>{{ maxLabel }}</span>
                        </div>
                    </div>
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
import { defineProps, defineEmits, computed } from "vue";
import QuestionContainer from "../ui/QuestionContainer.vue";
import QuestionLabel from "../ui/QuestionLabel.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Number,
        default: null,
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

// Get min value
const minValue = computed(() => {
    return settings.value.minValue || 0;
});

// Get max value
const maxValue = computed(() => {
    return settings.value.maxValue || 100;
});

// Get step value
const step = computed(() => {
    return settings.value.step || 1;
});

// Get min label
const minLabel = computed(() => {
    return settings.value.minLabel || minValue.value.toString();
});

// Get mid label
const midLabel = computed(() => {
    const midVal = Math.floor(
        (parseInt(minValue.value) + parseInt(maxValue.value)) / 2
    );
    return settings.value.midLabel || midVal.toString();
});

// Get max label
const maxLabel = computed(() => {
    return settings.value.maxLabel || maxValue.value.toString();
});

// Show mid label?
const showMidLabel = computed(() => {
    return settings.value.showMidLabel !== undefined
        ? settings.value.showMidLabel
        : true;
});

// Get formatted display value
const displayValue = computed(() => {
    if (props.modelValue === null) {
        return "-";
    }

    const format = settings.value.valueFormat || "{value}";
    return format.replace("{value}", props.modelValue);
});

// Update the value
const updateValue = (value) => {
    emit("update:modelValue", parseFloat(value));
};

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (
        props.question.required &&
        (props.modelValue === null || props.modelValue === undefined)
    ) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
/* Slider custom styling */
input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    height: 8px;
    background: #e5e7eb;
    border-radius: 5px;
    outline: none;
}

/* Thumb styling for Firefox */
input[type="range"]::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    border: none;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

/* Thumb styling for Chrome/Safari/Edge */
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #4f46e5;
    cursor: pointer;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

/* Track styling for Firefox */
input[type="range"]::-moz-range-progress {
    background: #4f46e5;
    height: 8px;
    border-radius: 5px;
}

/* Animations */
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
