<template>
    <div class="phone-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                    >
                        <span class="text-gray-500">+</span>
                    </div>
                    <input
                        type="tel"
                        class="w-full pl-7 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                        :class="{
                            'border-red-300': error,
                            'hover:border-indigo-300': !error,
                        }"
                        :placeholder="
                            question.placeholder || 'Masukkan nomor telepon...'
                        "
                        :value="modelValue"
                        @input="$emit('update:modelValue', $event.target.value)"
                        @blur="validate"
                    />
                </div>

                <p v-if="formatHint" class="mt-1 text-xs text-gray-500">
                    {{ formatHint }}
                </p>
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

// Get format hint
const formatHint = computed(() => {
    return settings.value.formatHint || "Contoh: 81234567890 (tanpa awalan 0)";
});

// Phone number regex
const phoneRegex = computed(() => {
    return settings.value.validation?.pattern || /^\d{8,15}$/;
});

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !props.modelValue.trim()) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    } else if (props.modelValue && !phoneRegex.value.test(props.modelValue)) {
        // Format validation
        isValid = false;
        errorMessage =
            settings.value.validation?.message || "Nomor telepon tidak valid.";
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
input {
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
}

input:focus {
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
