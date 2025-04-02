<template>
    <div class="email-question">
        <question-container>
            <question-label
                :question="question"
                :required="question.required"
                :help-text="question.helpText"
            />

            <div class="mt-3">
                <input
                    type="email"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                    :class="{
                        'border-red-300': error,
                        'hover:border-indigo-300': !error,
                    }"
                    :placeholder="
                        question.placeholder || 'Masukkan alamat email Anda...'
                    "
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
import { defineProps, defineEmits } from "vue";
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

// Email validation regex
const emailRegex =
    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

// Validate the input
const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation
    if (props.question.required && !props.modelValue.trim()) {
        isValid = false;
        errorMessage = "Pertanyaan ini wajib dijawab.";
    } else if (
        props.modelValue &&
        !emailRegex.test(props.modelValue.toLowerCase())
    ) {
        // Format validation
        isValid = false;
        errorMessage = "Mohon masukkan alamat email yang valid.";
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
