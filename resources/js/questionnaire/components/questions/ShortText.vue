<template>
    <div class="question-component">
        <label :for="inputId" class="block text-sm font-medium text-gray-700">
            {{ question.text }}
            <span v-if="question.required" class="text-red-500">*</span>
        </label>

        <p v-if="question.helpText" class="mt-1 text-sm text-gray-500">
            {{ question.helpText }}
        </p>

        <div class="mt-2">
            <input
                :id="inputId"
                type="text"
                :name="inputName"
                :placeholder="question.placeholder"
                :value="modelValue"
                @input="updateValue($event.target.value)"
                :required="question.required"
                :maxlength="
                    question.maxLength > 0 ? question.maxLength : undefined
                "
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
            />

            <div
                v-if="question.maxLength > 0"
                class="mt-1 text-xs text-gray-500 flex justify-end"
            >
                {{ modelValue.length }} / {{ question.maxLength }}
            </div>
        </div>

        <div v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed } from "vue";
import { v4 as uuidv4 } from "uuid";

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
    isBuilder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "validate"]);

// Unique ID for input field
const inputId = computed(() => `q-${props.question.id || uuidv4()}`);
const inputName = computed(() => `question_${props.question.id || ""}`);

// Fungsi untuk memperbarui nilai
const updateValue = (value) => {
    emit("update:modelValue", value);

    // Validate on change
    validate();
};

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
