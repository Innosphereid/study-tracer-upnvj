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
                            v-for="(column, colIdx) in question.columns"
                            :key="colIdx"
                            class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {{ column.text }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(row, rowIdx) in question.rows"
                        :key="rowIdx"
                        :class="rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                    >
                        <td class="px-4 py-4 text-sm text-gray-700 font-medium">
                            {{ row.text }}
                        </td>
                        <td
                            v-for="(column, colIdx) in question.columns"
                            :key="colIdx"
                            class="px-4 py-4 text-center"
                        >
                            <div class="flex justify-center">
                                <input
                                    :type="question.selectionType || 'radio'"
                                    :name="`matrix-${question.id}-row-${rowIdx}`"
                                    :value="column.value || column.text"
                                    :checked="
                                        isOptionSelected(
                                            rowIdx,
                                            column.value || column.text
                                        )
                                    "
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 transition-all duration-200"
                                    :class="
                                        question.selectionType === 'checkbox'
                                            ? 'rounded'
                                            : 'rounded-full'
                                    "
                                    @change="
                                        handleOptionChange(
                                            rowIdx,
                                            column.value || column.text
                                        )
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
import { ref, computed } from "vue";

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

    // For radio buttons (single selection per row)
    if (
        !props.question.selectionType ||
        props.question.selectionType === "radio"
    ) {
        newAnswers[rowIndex] = columnValue;
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
    }

    emit("update:modelValue", { answers: newAnswers });
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
