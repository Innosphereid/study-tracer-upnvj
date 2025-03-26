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

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th
                                class="w-1/4 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Pernyataan
                            </th>
                            <th
                                v-for="option in question.scale"
                                :key="option.value"
                                class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                {{ option.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(statement, index) in question.statements"
                            :key="statement.id"
                            :class="{ 'bg-gray-50': index % 2 === 0 }"
                        >
                            <td class="px-2 py-4 text-sm text-gray-900">
                                {{ statement.text }}
                            </td>
                            <td
                                v-for="option in question.scale"
                                :key="`${statement.id}-${option.value}`"
                                class="px-2 py-4 text-center"
                            >
                                <input
                                    type="radio"
                                    :name="`likert-${question.id}-${statement.id}`"
                                    :value="option.value"
                                    :id="`likert-${question.id}-${statement.id}-${option.value}`"
                                    v-model="responses[statement.id]"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                    @change="updateValue"
                                    :disabled="isBuilder"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <div v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, onMounted } from "vue";

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

// Internal state
const responses = ref({});

// Initialize responses from model value or create empty object
onMounted(() => {
    if (props.modelValue && props.modelValue.responses) {
        responses.value = { ...props.modelValue.responses };
    } else {
        // Initialize empty responses for each statement
        props.question.statements.forEach((statement) => {
            responses.value[statement.id] = null;
        });
    }
});

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal && newVal.responses) {
            responses.value = { ...newVal.responses };
        }
    },
    { deep: true }
);

// Watch for statement changes (in builder mode)
watch(
    () => props.question.statements,
    () => {
        // Ensure all statements have a response entry
        props.question.statements.forEach((statement) => {
            if (responses.value[statement.id] === undefined) {
                responses.value[statement.id] = null;
            }
        });

        // Clean up responses for statements that no longer exist
        const validStatementIds = props.question.statements.map((s) => s.id);
        Object.keys(responses.value).forEach((statementId) => {
            if (!validStatementIds.includes(statementId)) {
                delete responses.value[statementId];
            }
        });
    },
    { deep: true }
);

const updateValue = () => {
    emit("update:modelValue", { responses: { ...responses.value } });
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // Check if all statements have been answered (if required)
    if (props.question.required) {
        const unansweredStatements = props.question.statements.filter(
            (statement) => !responses.value[statement.id]
        );

        if (unansweredStatements.length > 0) {
            isValid = false;
            errorMessage =
                unansweredStatements.length === 1
                    ? "Harap jawab semua pernyataan."
                    : `Harap jawab semua ${unansweredStatements.length} pernyataan.`;
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
/* Custom responsive styling for likert scale */
@media (max-width: 640px) {
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
