<template>
    <div class="matrix-question">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border-collapse">
                <thead>
                    <tr>
                        <th
                            class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3"
                        ></th>
                        <th
                            v-for="(column, colIdx) in normalizedColumns"
                            :key="colIdx"
                            class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {{ column.text }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(row, rowIdx) in normalizedRows"
                        :key="rowIdx"
                        :class="rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                    >
                        <td class="px-4 py-4 text-sm text-gray-700 font-medium">
                            {{ row.text }}
                        </td>
                        <td
                            v-for="(column, colIdx) in normalizedColumns"
                            :key="colIdx"
                            class="px-4 py-4 text-center"
                        >
                            <div class="flex justify-center">
                                <input
                                    :type="question.selectionType || 'radio'"
                                    :name="`matrix-${question.id}-row-${rowIdx}`"
                                    :value="column.value"
                                    :checked="
                                        isOptionSelected(rowIdx, column.value)
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 transition-all duration-200"
                                    :class="
                                        question.selectionType === 'checkbox'
                                            ? 'rounded'
                                            : 'rounded-full'
                                    "
                                    @change="
                                        handleOptionChange(rowIdx, column.value)
                                    "
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <transition name="fade">
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({ answers: {} }),
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

// Log props for debugging
onMounted(() => {
    console.log("MatrixQuestion mounted with props:", {
        id: props.question.id,
        rows: props.question.rows,
        columns: props.question.columns,
        rowsOrder: props.question.rowsOrder,
        columnsOrder: props.question.columnsOrder,
        selectionType: props.question.selectionType,
        modelValue: props.modelValue,
    });

    // Initialize both formats if any selection exists
    initializeResponseFormats();
});

// Initialize both formats to ensure validation works
const initializeResponseFormats = () => {
    // Only proceed if we already have answers
    if (
        !props.modelValue?.answers ||
        Object.keys(props.modelValue.answers).length === 0
    ) {
        return;
    }

    // If we have answers but not responses/checkboxResponses, create them
    if (
        (!props.question.selectionType ||
            props.question.selectionType === "radio") &&
        (!props.modelValue.responses ||
            Object.keys(props.modelValue.responses).length === 0)
    ) {
        // Create responses from answers
        const responses = {};
        Object.entries(props.modelValue.answers).forEach(
            ([rowIndex, columnValue]) => {
                const rowData = normalizedRows.value[parseInt(rowIndex)];
                if (rowData && rowData.id) {
                    responses[rowData.id] = columnValue;
                }
            }
        );

        if (Object.keys(responses).length > 0) {
            // Add responses to model value
            emit("update:modelValue", {
                ...props.modelValue,
                responses,
            });
        }
    } else if (
        props.question.selectionType === "checkbox" &&
        (!props.modelValue.checkboxResponses ||
            Object.keys(props.modelValue.checkboxResponses).length === 0)
    ) {
        // Create checkboxResponses from answers
        const checkboxResponses = {};
        Object.entries(props.modelValue.answers).forEach(
            ([rowIndex, columnValues]) => {
                if (!Array.isArray(columnValues)) return;

                const rowData = normalizedRows.value[parseInt(rowIndex)];
                if (rowData && rowData.id) {
                    checkboxResponses[rowData.id] = {};

                    // Set selected columns
                    columnValues.forEach((columnValue) => {
                        const column = normalizedColumns.value.find(
                            (col) => col.value === columnValue
                        );
                        if (column && column.id) {
                            checkboxResponses[rowData.id][column.id] = true;
                        }
                    });
                }
            }
        );

        if (Object.keys(checkboxResponses).length > 0) {
            // Add checkboxResponses to model value
            emit("update:modelValue", {
                ...props.modelValue,
                checkboxResponses,
            });
        }
    }
};

// Computed property to normalize rows with proper sorting
const normalizedRows = computed(() => {
    if (!props.question.rows || !Array.isArray(props.question.rows)) {
        return [];
    }

    // Create a copy of the rows
    const rows = [...props.question.rows].map((row) => {
        // Ensure row has all required properties
        return {
            id: row.id || `row_${row.text}`,
            text: row.text,
            value: row.value || row.text,
        };
    });

    // Sort rows if needed
    if (props.question.rowsOrder === "desc") {
        rows.sort((a, b) => b.text.localeCompare(a.text));
    }

    return rows;
});

// Computed property to normalize columns with proper sorting
const normalizedColumns = computed(() => {
    if (!props.question.columns || !Array.isArray(props.question.columns)) {
        return [];
    }

    // Create a copy of the columns
    const columns = [...props.question.columns].map((column) => {
        // Ensure column has all required properties
        return {
            id: column.id || `column_${column.text}`,
            text: column.text,
            value: column.value || column.text,
        };
    });

    // Sort columns if needed
    if (props.question.columnsOrder === "desc") {
        columns.sort((a, b) => b.text.localeCompare(a.text));
    }

    return columns;
});

// Check if an option is selected for a specific row and column
const isOptionSelected = (rowIndex, columnValue) => {
    if (!props.modelValue || !props.modelValue.answers) return false;

    const rowAnswers = props.modelValue.answers[rowIndex];

    // For radio buttons (single selection per row)
    if (
        !props.question.selectionType ||
        props.question.selectionType === "radio"
    ) {
        return rowAnswers === columnValue;
    }

    // For checkboxes (multiple selections per row)
    if (props.question.selectionType === "checkbox") {
        return Array.isArray(rowAnswers) && rowAnswers.includes(columnValue);
    }

    return false;
};

// Handle selection change
const handleOptionChange = (rowIndex, columnValue) => {
    const newAnswers = { ...(props.modelValue?.answers || {}) };
    const rowData = normalizedRows.value[rowIndex];

    // Create/update responses object for validation format
    const responses = { ...(props.modelValue?.responses || {}) };

    // For radio buttons (single selection per row)
    if (
        !props.question.selectionType ||
        props.question.selectionType === "radio"
    ) {
        newAnswers[rowIndex] = columnValue;

        // Update the responses format for validation
        if (rowData && rowData.id) {
            responses[rowData.id] = columnValue;
        }
    }
    // For checkboxes (multiple selections per row)
    else if (props.question.selectionType === "checkbox") {
        // Initialize as array if not already
        if (!Array.isArray(newAnswers[rowIndex])) {
            newAnswers[rowIndex] = [];
        }

        const index = newAnswers[rowIndex].indexOf(columnValue);

        // Toggle selection
        if (index > -1) {
            newAnswers[rowIndex].splice(index, 1);
        } else {
            newAnswers[rowIndex].push(columnValue);
        }

        // Update the checkboxResponses format for validation
        const checkboxResponses = {
            ...(props.modelValue?.checkboxResponses || {}),
        };
        if (rowData && rowData.id) {
            if (!checkboxResponses[rowData.id]) {
                checkboxResponses[rowData.id] = {};
            }

            // Find the column that matches this value
            const column = normalizedColumns.value.find(
                (col) => col.value === columnValue
            );
            if (column && column.id) {
                // Toggle the checkbox state - fix the logic
                checkboxResponses[rowData.id][column.id] = index === -1; // If index is -1, we're adding it
            }
        }

        // Emit both formats
        emit("update:modelValue", {
            answers: newAnswers,
            checkboxResponses: checkboxResponses,
        });
        return;
    }

    // Emit both component format and validation format
    emit("update:modelValue", {
        answers: newAnswers,
        responses: responses,
    });
};
</script>

<style scoped>
.matrix-question table {
    border-collapse: separate;
    border-spacing: 0;
}

.matrix-question th,
.matrix-question td {
    border: 1px solid #e5e7eb;
}

.matrix-question th:first-child {
    border-top-left-radius: 0.5rem;
}

.matrix-question th:last-child {
    border-top-right-radius: 0.5rem;
}

.matrix-question tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

.matrix-question tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}

/* Radio/checkbox styling */
.matrix-question input[type="radio"],
.matrix-question input[type="checkbox"] {
    cursor: pointer;
    position: relative;
    transition: all 0.15s ease-in-out;
}

.matrix-question input[type="radio"]:checked,
.matrix-question input[type="checkbox"]:checked {
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

/* Make table responsive */
@media (max-width: 640px) {
    .matrix-question {
        overflow-x: auto;
    }
}
</style>
