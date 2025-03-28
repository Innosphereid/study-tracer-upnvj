<template>
    <div class="likert-question">
        <!-- Horizontal likert scale layout -->
        <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
            <div class="flex items-center justify-between w-full">
                <div class="flex flex-1 justify-between">
                    <div
                        v-for="option in likertScales"
                        :key="option.value"
                        class="flex flex-col items-center mx-1"
                    >
                        <input
                            type="radio"
                            :name="`likert-${question.id}`"
                            :value="option.value"
                            :id="`likert-${question.id}-${option.value}`"
                            :checked="
                                modelValue?.responses?.[0] === option.value
                            "
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer"
                            @change="updateStatementValue(0, option.value)"
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
            <span class="text-xs text-gray-500">
                {{
                    question.leftLabel ||
                    likertScales[0]?.label ||
                    "Sangat Tidak Setuju"
                }}
            </span>
            <span class="text-xs text-gray-500">
                {{
                    question.rightLabel ||
                    likertScales[likertScales.length - 1]?.label ||
                    "Sangat Setuju"
                }}
            </span>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { computed } from "vue";

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
});

const emit = defineEmits(["update:modelValue"]);

// Get likert scale from question or use default
const likertScales = computed(() => {
    // Check for scale in the question properties
    if (props.question.scale && props.question.scale.length > 0) {
        return props.question.scale;
    }

    // Look for scale in settings
    if (
        props.question.settings &&
        typeof props.question.settings === "string"
    ) {
        try {
            const settings = JSON.parse(props.question.settings);
            if (settings.scale && settings.scale.length > 0) {
                return settings.scale;
            }
        } catch (e) {
            console.error("Error parsing question settings:", e);
        }
    }

    // Default 5-point Likert scale
    return [
        { value: 1, label: "Sangat Tidak Setuju" },
        { value: 2, label: "Tidak Setuju" },
        { value: 3, label: "Netral" },
        { value: 4, label: "Setuju" },
        { value: 5, label: "Sangat Setuju" },
    ];
});

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
};
</script>

<style scoped>
.likert-question {
    margin-top: 1rem;
}

/* Radio button styling */
input[type="radio"] {
    cursor: pointer;
    transition: transform 0.2s;
}

input[type="radio"]:checked {
    transform: scale(1.2);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive styles */
@media (max-width: 640px) {
    .likert-question {
        overflow-x: auto;
    }
}
</style>
