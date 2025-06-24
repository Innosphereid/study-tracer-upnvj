<template>
    <div class="short-text-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <input
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    :class="{
                        'border-red-300': error,
                        'hover:border-indigo-300': !error,
                    }"
                    :placeholder="
                        question.placeholder || 'Ketik jawaban Anda di sini...'
                    "
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @blur="validate"
                    :maxlength="
                        question.maxLength > 0 ? question.maxLength : undefined
                    "
                />

                <div
                    v-if="question.maxLength > 0"
                    class="mt-1 text-xs text-gray-500 flex justify-end"
                >
                    {{ modelValue.length || 0 }} / {{ question.maxLength }}
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
import { defineProps, defineEmits, ref, computed } from "vue";
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

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !props.modelValue.trim()) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    }

    // Max length validation
    if (
        props.question.maxLength > 0 &&
        props.modelValue.length > props.question.maxLength
    ) {
        isValid = false;
        errorMessage = `Jawaban tidak boleh lebih dari ${props.question.maxLength} karakter.`;
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
