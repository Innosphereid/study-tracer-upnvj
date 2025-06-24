<template>
    <div class="number-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <input
                    type="number"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    :class="{
                        'border-red-300': error,
                        'hover:border-indigo-300': !error,
                    }"
                    :placeholder="question.placeholder || 'Masukkan angka...'"
                    :value="modelValue"
                    @input="
                        $emit(
                            'update:modelValue',
                            $event.target.value
                                ? parseFloat($event.target.value)
                                : null
                        )
                    "
                    @blur="validate"
                    :min="minValue"
                    :max="maxValue"
                    :step="step"
                />

                <div
                    v-if="hasMinMax"
                    class="mt-1 text-xs text-gray-500 flex justify-between"
                >
                    <span v-if="hasMin">Min: {{ minValue }}</span>
                    <span v-if="hasMax">Max: {{ maxValue }}</span>
                </div>
            </div>

            <transition name="fade">
                <p v-if="error" class="mt-1 text-sm text-red-600">
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

// Check if min value is set
const hasMin = computed(() => {
    return (
        settings.value.minValue !== undefined &&
        settings.value.minValue !== null
    );
});

// Check if max value is set
const hasMax = computed(() => {
    return (
        settings.value.maxValue !== undefined &&
        settings.value.maxValue !== null
    );
});

// Check if either min or max is set
const hasMinMax = computed(() => {
    return hasMin.value || hasMax.value;
});

// Get min value
const minValue = computed(() => {
    return settings.value.minValue !== undefined ? settings.value.minValue : "";
});

// Get max value
const maxValue = computed(() => {
    return settings.value.maxValue !== undefined ? settings.value.maxValue : "";
});

// Get step value
const step = computed(() => {
    return settings.value.step || "any";
});

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (
        props.question.required &&
        (props.modelValue === null || props.modelValue === "")
    ) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    } else if (props.modelValue !== null) {
        // Min validation
        if (hasMin.value && props.modelValue < minValue.value) {
            isValid = false;
            errorMessage = `Nilai minimum adalah ${minValue.value}.`;
        }

        // Max validation
        if (hasMax.value && props.modelValue > maxValue.value) {
            isValid = false;
            errorMessage = `Nilai maksimum adalah ${maxValue.value}.`;
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
input[type="number"] {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
}

input[type="number"]:focus {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

/* Hide number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

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
