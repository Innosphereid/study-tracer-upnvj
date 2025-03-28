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
                <!-- Horizontal likert scale layout -->
                <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex flex-1 justify-between">
                            <div
                                v-for="option in question.scale"
                                :key="option.value"
                                class="flex flex-col items-center mx-1"
                            >
                                <input
                                    type="radio"
                                    :name="`likert-${question.id}`"
                                    :value="option.value"
                                    :id="`likert-${question.id}-${option.value}`"
                                    :checked="
                                        modelValue?.responses?.[0] ===
                                        option.value
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                                    @change="
                                        updateStatementValue(0, option.value)
                                    "
                                    :disabled="isBuilder"
                                />
                                <label
                                    :for="`likert-${question.id}-${option.value}`"
                                    class="text-xs text-gray-600 mt-1 text-center whitespace-nowrap"
                                >
                                    {{ option.value }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scale labels -->
                <div class="flex justify-between mt-2 px-2">
                    <span class="text-xs text-gray-500">{{ minLabel }}</span>
                    <span class="text-xs text-gray-500">{{ maxLabel }}</span>
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
        type: Object,
        default: () => ({ responses: {} }),
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

// Computed property to get statements from the question
const statements = computed(() => {
    // First check if statements exist directly
    if (props.question.statements && props.question.statements.length > 0) {
        return props.question.statements;
    }

    // Then check settings object
    if (
        props.question.settings &&
        typeof props.question.settings === "string"
    ) {
        try {
            const settings = JSON.parse(props.question.settings);
            if (settings.statements && settings.statements.length > 0) {
                return settings.statements;
            }

            // If no statements found but we have text, create a statement with the question text
            if (settings.text) {
                return [{ id: "default-statement", text: settings.text }];
            }
        } catch (e) {
            console.error("Error parsing question settings:", e);
        }
    }

    // Fallback to create a statement from the question text if available
    if (props.question.text) {
        return [{ id: "default-statement", text: props.question.text }];
    }

    // Default empty statement as fallback
    return [{ id: "default-statement", text: "Pernyataan 1" }];
});

// Computed properties for labels
const minLabel = computed(() => {
    if (props.question.scale && props.question.scale.length > 0) {
        return props.question.scale[0].label;
    }
    return "Sangat Tidak Setuju";
});

const maxLabel = computed(() => {
    if (props.question.scale && props.question.scale.length > 0) {
        return props.question.scale[props.question.scale.length - 1].label;
    }
    return "Sangat Setuju";
});

// Get the current value for a statement
const getStatementValue = (statementIndex) => {
    if (!props.modelValue || !props.modelValue.responses) return null;
    return props.modelValue.responses[statementIndex];
};

// Update a statement's value
const updateStatementValue = (statementIndex, value) => {
    const newResponses = { ...(props.modelValue?.responses || {}) };
    newResponses[statementIndex] = value;
    emit("update:modelValue", { responses: newResponses });
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Required validation - check if all statements have responses
    if (props.question.required) {
        const responses = props.modelValue?.responses || {};

        if (Object.keys(responses).length < statements.value.length) {
            isValid = false;
            errorMessage = "Pertanyaan ini wajib dijawab.";
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
/* Radio button styling */
input[type="radio"] {
    cursor: pointer;
    transition: transform 0.2s;
}

input[type="radio"]:checked {
    transform: scale(1.2);
}

/* Responsive styling */
@media (max-width: 640px) {
    .flex-1 {
        overflow-x: auto;
    }
}
</style>
