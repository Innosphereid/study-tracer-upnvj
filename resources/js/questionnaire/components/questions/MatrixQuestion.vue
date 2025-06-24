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
                                <!-- Empty corner cell -->
                            </th>
                            <th
                                v-for="column in question.columns"
                                :key="column.id"
                                class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                {{ column.text }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(row, rowIndex) in question.rows"
                            :key="row.id"
                            :class="{ 'bg-gray-50': rowIndex % 2 === 0 }"
                        >
                            <td class="px-2 py-4 text-sm text-gray-900">
                                {{ row.text }}
                            </td>
                            <td
                                v-for="column in question.columns"
                                :key="`${row.id}-${column.id}`"
                                class="px-2 py-4 text-center"
                            >
                                <template
                                    v-if="question.matrixType === 'radio'"
                                >
                                    <input
                                        type="radio"
                                        :name="`matrix-${question.id}-${row.id}`"
                                        :value="column.id"
                                        v-model="responses[row.id]"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        @change="updateValue"
                                        :disabled="isBuilder"
                                    />
                                </template>
                                <template
                                    v-else-if="
                                        question.matrixType === 'checkbox'
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        :name="`matrix-${question.id}-${row.id}-${column.id}`"
                                        :value="column.id"
                                        v-model="
                                            checkboxResponses[row.id][column.id]
                                        "
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                        @change="updateCheckboxValue"
                                        :disabled="isBuilder"
                                    />
                                </template>
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

// Internal state for radio type matrix
const responses = ref({});

// Internal state for checkbox type matrix
const checkboxResponses = ref({});

// Initialize responses from model value or create empty object
onMounted(() => {
    initializeResponses();
});

const initializeResponses = () => {
    if (props.question.matrixType === "radio") {
        if (props.modelValue && props.modelValue.responses) {
            responses.value = { ...props.modelValue.responses };
        } else {
            // Initialize empty responses for each row
            props.question.rows.forEach((row) => {
                responses.value[row.id] = null;
            });
        }
    } else if (props.question.matrixType === "checkbox") {
        if (props.modelValue && props.modelValue.checkboxResponses) {
            checkboxResponses.value = JSON.parse(
                JSON.stringify(props.modelValue.checkboxResponses)
            );
        } else {
            // Initialize empty checkbox responses for each row
            props.question.rows.forEach((row) => {
                checkboxResponses.value[row.id] = {};
                props.question.columns.forEach((column) => {
                    checkboxResponses.value[row.id][column.id] = false;
                });
            });
        }
    }
};

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        if (props.question.matrixType === "radio") {
            if (newVal && newVal.responses) {
                responses.value = { ...newVal.responses };
            }
        } else if (props.question.matrixType === "checkbox") {
            if (newVal && newVal.checkboxResponses) {
                checkboxResponses.value = JSON.parse(
                    JSON.stringify(newVal.checkboxResponses)
                );
            }
        }
    },
    { deep: true }
);

// Watch for row/column changes (in builder mode)
watch(
    () => [
        props.question.rows,
        props.question.columns,
        props.question.matrixType,
    ],
    () => {
        initializeResponses();
    },
    { deep: true }
);

const updateValue = () => {
    emit("update:modelValue", { responses: { ...responses.value } });
    validate();
};

const updateCheckboxValue = () => {
    emit("update:modelValue", {
        checkboxResponses: JSON.parse(JSON.stringify(checkboxResponses.value)),
    });
    validate();
};

const validate = () => {
    let isValid = true;
    let errorMessage = "";

    // For radio type matrix
    if (props.question.matrixType === "radio" && props.question.required) {
        const unansweredRows = props.question.rows.filter(
            (row) => !responses.value[row.id]
        );

        if (unansweredRows.length > 0) {
            isValid = false;
            errorMessage =
                unansweredRows.length === 1
                    ? "Harap jawab semua baris."
                    : `Harap jawab semua ${unansweredRows.length} baris.`;
        }
    }

    // For checkbox type matrix
    if (props.question.matrixType === "checkbox" && props.question.required) {
        const unansweredRows = props.question.rows.filter((row) => {
            // Check if at least one checkbox is selected in this row
            return !props.question.columns.some(
                (column) => checkboxResponses.value[row.id][column.id]
            );
        });

        if (unansweredRows.length > 0) {
            isValid = false;
            errorMessage =
                unansweredRows.length === 1
                    ? "Harap pilih minimal satu opsi untuk setiap baris."
                    : `Harap pilih minimal satu opsi untuk setiap ${unansweredRows.length} baris.`;
        }
    }

    emit("validate", { isValid, errorMessage });
    return isValid;
};
</script>

<style scoped>
/* Custom responsive styling for matrix */
@media (max-width: 640px) {
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
