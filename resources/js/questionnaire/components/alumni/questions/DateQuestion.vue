<template>
    <div class="date-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <input
                    type="date"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    :class="{
                        'border-red-300': error,
                        'hover:border-indigo-300': !error,
                    }"
                    :min="minDate"
                    :max="maxDate"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @blur="validate"
                />
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

// Get min date if set
const minDate = computed(() => {
    return settings.value.minDate || "";
});

// Get max date if set
const maxDate = computed(() => {
    return settings.value.maxDate || "";
});

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !props.modelValue) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    // Min date validation
    if (props.modelValue && minDate.value && props.modelValue < minDate.value) {
        isValid = false;
        errorMessage = `Tanggal tidak boleh sebelum ${minDate.value}.`;
    }

    // Max date validation
    if (props.modelValue && maxDate.value && props.modelValue > maxDate.value) {
        isValid = false;
        errorMessage = `Tanggal tidak boleh setelah ${maxDate.value}.`;
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
input[type="date"] {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
}

input[type="date"]:focus {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
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
